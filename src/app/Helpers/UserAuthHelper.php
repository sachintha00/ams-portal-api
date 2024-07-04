<?php

namespace App\Helpers;

use App\Mail\UserAuthenticationVerifyEmail;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Client as OClient;

class UserAuthHelper
{
    public static function getAuthAccessRefreshToken($email, $password)
    {
        // DB::beginTransaction();
        try {
            $httpClient = new HttpClient();
            $oClient = OClient::where('password_client', 1)->first();
            $hostIpAddress = config('passport.app_host.ip');
            $hostPort = config('passport.app_host.port');
            
            $response = $httpClient->post("http://{$hostIpAddress}:{$hostPort}/oauth/token", [
                'headers' => [
                    'X-API-Key' => '{{token}}',
                ],
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'username' => $email,
                    'password' => $password,
                    'scope' => '',
                ],
            ]);


            $result = json_decode($response->getBody(), true);

            // DB::commit();

            return $result;
        } catch (\Exception $e) {
            // DB::rollBack();
            return response()->json(['error' => 'Failed to obtain access token', 'message' => $e->getMessage()], 500);
        }
    }

    public static function getAuthRefreshToken($refreshToken)
    {
        try {
            $httpClient = new HttpClient();
            $oClient = OClient::where('password_client', 1)->first();
            $hostIpAddress = config('passport.app_host.ip');
            $hostPort = config('passport.app_host.port');

            $response = $httpClient->post("http://{$hostIpAddress}:{$hostPort}/oauth/token", [
                'headers' => [
                    'X-API-Key' => '{{token}}',
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'refresh_token' => $refreshToken,
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            return $result;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to obtain access token', 'message' => $e->getMessage()], 500);
        }
    }

    public static function sendVerificationEmail($createdUser)
    {
        // DB::beginTransaction();
        try {
            $emailActivationCode = sprintf("%03d-%03d", rand(0, 999), rand(0, 999));
            $emailVerifyToken = EmailTokenCreateAndVerifyHelper::createEmailVerificationToken($createdUser, $emailActivationCode);

            $activateUrl = "http://localhost:3001/email_activation_code={$emailActivationCode}";

            Mail::to($createdUser->email)->send(new UserAuthenticationVerifyEmail($emailActivationCode, $activateUrl));

            // DB::commit();

            return $emailVerifyToken;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to obtain access token', 'message' => $e->getMessage()], 500);
        }
    }
}