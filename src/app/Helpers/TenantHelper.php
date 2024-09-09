<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Models\tenants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TenantHelper
{
    // public static function generateTenantDbName($registeredUserEmail): String
    // {
    //     $dbSuffix = substr(hash('sha256', $registeredUserEmail), 0, 5);
    //     return 'tenant_' . $dbSuffix;
    // }
    public static function generateTenantDbName($registeredUserEmail): String
    {
        do {
            $dbSuffix = substr(hash('sha256', $registeredUserEmail), 0, 5);
            $databasename = 'tenant_' . $dbSuffix;

            $exists = DB::table('tenants')->where('db_name', $databasename)->exists();

        } while ($exists);

        return $databasename;
    }

    public static function generateTenantDbUserName($registeredUserEmail): String
    {
        do {
            $dbUserSuffix = substr(hash('sha256', $registeredUserEmail), 0, 5);
            $randomString = Str::random(5); 
            $databaseusername = '_' . $randomString . $dbUserSuffix;

            $exists = DB::table('tenants')->where('db_user', $databaseusername)->exists();

        } while ($exists);

        return $databaseusername;
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

    public static function setupTenantDatabase($tenantUser, $packageType, $appUserEmail): void
    {
        try {
            if ($packageType == "ENTERPRISE" && $tenantUser) {
                $tenantDbHost = env('DB_HOST');
                $tenantDbName = self::generateTenantDbName($tenantUser->email);
                $tenantDbUserName = self::generateTenantDbUserName($tenantUser->email);
                
                // Generate a more robust password without special characters
                $tenantDbUserPassword = bin2hex(random_bytes(8));

                if (!$tenantDbHost || !$tenantDbName || !$tenantDbUserName || !$tenantDbUserPassword) {
                    throw new \Exception("Missing required database information.");
                }
    
                if ($tenantDbHost && $tenantDbName && $tenantDbUserName && $tenantDbUserPassword) {
    
                    // 1. Create tenant user and database
                    DB::statement("CREATE USER \"$tenantDbUserName\" WITH PASSWORD '$tenantDbUserPassword';");
                    DB::statement("CREATE DATABASE \"$tenantDbName\" OWNER \"$tenantDbUserName\";");
                    DB::statement("GRANT ALL PRIVILEGES ON DATABASE \"$tenantDbName\" TO \"$tenantDbUserName\";");
    
                    // Store tenant information
                    $tenant = tenants::create([
                        'tenant_name' => $tenantUser->name,
                        'address' => $tenantUser->address,
                        'contact_no' => $tenantUser->contact_no,
                        'contact_Person_no' => $tenantUser->contact_person,
                        'email' => $tenantUser->email,
                        'website' => $tenantUser->website,
                        'activation_code' => null,
                        'package' => $packageType,
                        'db_host' => $tenantDbHost,
                        'db_name' => $tenantDbName,
                        'db_user' => $tenantDbUserName,
                        'db_password' => $tenantDbUserPassword,
                    ]);
    
                    // 2. Dynamically configure tenant connection
                    Config::set("database.connections.tenant", [
                        'driver' => 'pgsql',
                        'host' => $tenantDbHost,
                        'port' => env('DB_PORT', '5432'),
                        'database' => $tenant->db_name,
                        'username' => $tenant->db_user,
                        'password' => $tenant->db_password,
                        'charset' => 'utf8',
                        'prefix' => '',
                        'schema' => 'public',
                        'sslmode' => 'prefer',
                    ]);

                    // 3. Test tenant DB connection
                    try {
                        DB::connection('tenant')->getPdo();
                        \Log::info("Connection to tenant DB successful.");
                    } catch (\Exception $e) {
                        throw new \Exception("Failed to connect to tenant DB: " . $e->getMessage());
                    }
    
                    // 4. Temporarily set the tenant connection as the default
                    $originalDefaultConnection = Config::get('database.default');
                    Config::set('database.default', 'tenant');
    
                    try {
                        Artisan::call('migrate', [
                            '--path' => 'database/migrations/tenant',
                            '--force' => true,
                        ]);
                        Artisan::call('passport:install');
                    } catch (\Exception $e) {
                        throw new \Exception("Failed to run migrations or install Passport: " . $e->getMessage());
                    }

                    // 5. Create the tenant's user
                    $userData = [
                        'user_name' => $tenantUser->user_name,
                        'email' => $tenantUser->email == $appUserEmail ? $tenantUser->email : $appUserEmail,
                        'name' => $tenantUser->name,
                        'contact_no' => $tenantUser->contact_no,
                        'contact_person' => $tenantUser->contact_person,
                        'website' => $tenantUser->website,
                        'address' => $tenantUser->address,
                        'password' => $tenantUser->password,
                        'is_owner' => $tenantUser->email == $appUserEmail ? true : false,
                        'is_app_user' => true,
                        'tenant_id' => $tenant->id,
                    ];

                    User::create($userData);

                    Artisan::call('db:seed', [
                        '--class' => 'TenantDBSeeder',
                    ]);
    
                    // 7. Revert back to the original default connection
                    Config::set('database.default', $originalDefaultConnection);
    
                    $user = User::findOrFail($tenantUser->id);
                    $user->tenant_id = $tenant->id;
                    $user->save();

                    if ($tenantUser->email != $appUserEmail) {
                        User::create([
                            'user_name' => $tenantUser->name,
                            'email' => $appUserEmail,
                            'name' => $tenantUser->name,
                            'contact_no' => $tenantUser->contact_no,
                            'contact_person' => $tenantUser->contact_person,
                            'website' => $tenantUser->website,
                            'address' => $tenantUser->address,
                            'password' => $tenantUser->password,
                            'tenant_id' => $tenant->id,
                        ]);
                    }

                    // Commit transaction if everything is successful
                    DB::commit();
                }
            } elseif ($packageType == "INDIVIDUAL" && $tenantUser) {
                $tenantDbHost = env('DB_HOST');
                $tenantDbName = env('DB_DATABASE');
                $tenantDbUserName = env('DB_USERNAME');
                $tenantDbUserPassword = env('DB_PASSWORD');

                if (!$tenantDbHost || !$tenantDbName || !$tenantDbUserName || !$tenantDbUserPassword) {
                    throw new \Exception("Missing required database credentials for individual package.");
                }

                if ($tenantDbHost && $tenantDbName && $tenantDbUserName && $tenantDbUserPassword) {
                    // Store tenant information
                    $tenant = tenants::create([
                        'tenant_name' => $tenantUser->name,
                        'address' => $tenantUser->address,
                        'contact_no' => $tenantUser->contact_no,
                        'contact_Person_no' => $tenantUser->contact_person,
                        'email' => $tenantUser->email,
                        'website' => $tenantUser->website,
                        'activation_code' => null,
                        'package' => $packageType,
                        'db_host' => $tenantDbHost,
                        'db_name' => $tenantDbName,
                        'db_user' => $tenantDbUserName,
                        'db_password' => $tenantDbUserPassword,
                    ]);

                    $user = User::findOrFail($tenantUser->id);
                    $user->tenant_id = $tenant->id;
                    $user->save();

                    if ($tenantUser->email != $appUserEmail) {
                        User::create([
                            'user_name' => $tenantUser->name,
                            'email' => $appUserEmail,
                            'name' => $tenantUser->name,
                            'contact_no' => $tenantUser->contact_no,
                            'contact_person' => $tenantUser->contact_person,
                            'website' => $tenantUser->website,
                            'address' => $tenantUser->address,
                            'password' => $tenantUser->password,
                            'tenant_id' => $tenant->id,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            \Log::error("Error setting up tenant database: " . $e->getMessage());
            throw $e;
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