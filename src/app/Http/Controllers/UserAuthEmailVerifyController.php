<?php

namespace App\Http\Controllers;

use App\Helpers\EmailTokenCreateAndVerifyHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserAuthEmailVerifyController extends Controller
{
    public function verifyUserAuthEmail(Request $request){
        try {
            $emailVerficationToken = $request->email_verify_token;
            $emailVerficationCode = $request->email_verify_code;
            
            $payload = EmailTokenCreateAndVerifyHelper::verifyEmailVerificationToken($emailVerficationToken);

            if(!$payload){
                return response()->json([
                    'status' => true,
                    'message' => 'Invalid Token',
                ], Response::HTTP_NOT_FOUND);
            }

            if(!Hash::check($emailVerficationCode, $payload['eac'])){
                return response()->json([
                    'status' => true,
                    'message' => "Didn't match. please retry!",
                ], Response::HTTP_FORBIDDEN);
            }

            $user = User::findOrFail(base64_decode($payload['uid']));

            $user->email_verified_at = now();
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Email Activated',
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}