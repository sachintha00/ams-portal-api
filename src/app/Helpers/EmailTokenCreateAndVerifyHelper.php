<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmailTokenCreateAndVerifyHelper
{
    public static function createEmailVerificationToken(User $user, string $emailActivationCode)
    {
        $secretKey = 'test';
        try {
            $payload = [
                'uid' => base64_encode($user->id),
                'eac' => Hash::make($emailActivationCode),
            ];
        
            $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode($payload));
            $signature = hash_hmac('sha256', "$header.$payload", $secretKey, false);

            return "$header.$payload." . base64_encode($signature);
        
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create email verification token', 'message' => $e->getMessage()], 500);
        }
    }

    public static function verifyEmailVerificationToken($emailVerificationToken)
    {
        $secretKey = 'test';
        try {
            $tokenParts = explode('.', $emailVerificationToken);

            $header = $tokenParts[0];
            $payload = $tokenParts[1];
            $signature = $tokenParts[2];
            $expectedSignature = hash_hmac('sha256', "$header.$payload", $secretKey, false);

            if (hash_equals(base64_decode($signature), $expectedSignature)) {
                return json_decode(base64_decode($payload), true);
            }

            return null;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 500);
        }
    }
}