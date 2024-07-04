<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TenantHelper
{
    public static function generateTenantDbName($registeredUserEmail): String
    {
        $dbSuffix = substr(hash('sha256', $registeredUserEmail), 0, 5);
        return 'tenant_' . $dbSuffix;
    }

    public static function sendPostRequest($email, $dbname)
    {
        try {
            $response = Http::post('http://213.199.44.42:8001/api/v1/write-tenant-env-file', [
                'file_name' => $email,
                'db_name' => $dbname,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->body()
                ], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public static function setupTenantDatabase($tenantDbName, $tenantUser): void
    {
        try {
            Artisan::call('tenant:database:create', ['name' => $tenantDbName]);

            Config::set("database.connections.$tenantDbName", [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '5432'),
                'database' => $tenantDbName,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'unix_socket' => env('DB_SOCKET', ''), 
                'charset' => 'utf8',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            $originalDefaultConnection = Config::get('database.default');
            Config::set('database.default', $tenantDbName);

            
            Artisan::call('migrate', [
                '--database' => $tenantDbName,
                '--path' => 'database/migrations/tenant',
            ]);
            Artisan::call('passport:install');

            User::create($tenantUser);

            Artisan::call('db:seed', [
                '--database' => $tenantDbName,
                '--class' => 'TenantDBSeeder',
            ]);

            Config::set('database.default', $originalDefaultConnection);
        } catch (\Throwable $th) {
            // self::rollbackChanges($tenantDbName);
            throw $th;
        }
    }

    private static function rollbackChanges($tenantDbName): void
    {
        try {
            DB::statement("DROP DATABASE IF EXISTS $tenantDbName");
        } catch (\Exception $e) {
            throw $e;
        }
    }
}