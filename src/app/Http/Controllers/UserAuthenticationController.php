<?php

namespace App\Http\Controllers;

use App\Helpers\TenantHelper;
use App\Helpers\UserAuthHelper;
use App\Http\Requests\AuthLoginRequest;
use App\Models\User;
use App\Models\tenants;
use App\Http\Requests\UserAuthRequest;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;
use Illuminate\Support\Str;

class UserAuthenticationController extends Controller
{
    public function registerNewUser(UserAuthRequest $request)
    {
        // DB::beginTransaction(); 
        try {
            $validatedUser = $request->validated();

            $packageType = $validatedUser['package'];
            $email = $validatedUser['email'];
            $appUserEmail = $validatedUser['app_user_email'];
            $validatedUser['password'] = bcrypt($validatedUser['password']);
            $validatedUser['is_owner'] = true; 

            $createdUser = User::create($validatedUser);

            // If the user's email is different from the app user email, create a secondary user
            if ($email != $appUserEmail) {
                $tenantUser = User::findOrFail($createdUser->id);
                $tenantUser->is_app_user = false;
                $tenantUser->save();
            }

            TenantHelper::setupTenantDatabase($createdUser, $packageType, $appUserEmail);

            $emailVerifyToken = UserAuthHelper::sendVerificationEmail($createdUser);

            // DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'email_verification_token' => $emailVerifyToken,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            // DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create user',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function loginUser(AuthLoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'is_owner' => true])) {
            $user = Auth::user();
            $tokensResult = UserAuthHelper::getAuthAccessRefreshToken($request->email, $request->password);
            $accessToken = $tokensResult['access_token'];
            $refreshToken = $tokensResult['refresh_token'];

            // return response()->json([
            //     'status' => true,
            //     'user' => $user,
            //     'access_token' => $accessToken,
            //     'refresh_token' => $refreshToken,
            // ], Response::HTTP_OK);

            $response = response()->json([
                'status' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ], Response::HTTP_OK);

            $response->cookie('accessToken', $accessToken, config('auth.token_lifetime'), '/', null, null, true); // HTTP-only
            $response->cookie('refreshToken', $refreshToken, config('auth.token_lifetime'), '/', null, null, true); // HTTP-only

            return $response;
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getUserDetails()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            return Response(['status' => true, 'data' => $user], 200);
        }
        return Response(['status' => false, 'message' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    public function userLogout()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $tokens = $user->tokens->pluck('id');
            Token::whereIn('id', $tokens)
                ->update(['revoked' => true]);

            return response()->json([
                'status' => true,
                'message' => 'User logged out successfully'
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => false,
            'message' => 'unauthorized'
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function refreshToken(Request $request)
    {
        try {
            if (!$request->header('Authorization')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Authorization header is missing. Please include the Authorization header with your request.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $token = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $token);

            $newAccessTokenResult = UserAuthHelper::getAuthRefreshToken($token);
            $accessToken = $newAccessTokenResult['access_token'];
            $refreshToken = $newAccessTokenResult['refresh_token'];

            return response()->json([
                'status' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create user',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}