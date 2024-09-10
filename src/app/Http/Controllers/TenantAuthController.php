<?php

namespace App\Http\Controllers;

use App\Helpers\UserAuthHelper;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\TenantRegisterdDataSaveRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantAuthController extends Controller
{
    public function tenantLoginUser(AuthLoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $tokensResult = UserAuthHelper::getAuthAccessRefreshToken($request->email, $request->password);
            $accessToken = $tokensResult['access_token'];
            $refreshToken = $tokensResult['refresh_token'];

            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'tenant_db_name' => $user->tenant_db_name,
            ];

            return response()->json([
                'status' => true,
                'message' => "User login success!",
                'user' => $userData,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function tenantUserRegister(TenantRegisterdDataSaveRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedUser = $request->validated();
            // $validatedUser['password'] = bcrypt($validatedUser['password']);
 
            User::create($validatedUser);

            DB::commit(); 

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create user',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
