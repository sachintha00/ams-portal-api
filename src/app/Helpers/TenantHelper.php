<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class TenantHelper
{
    public static function generateTenantDbName($registeredUserEmail): String
    {
        $dbSuffix = substr(hash('sha256', $registeredUserEmail), 0, 5);
        return 'tenant_' . $dbSuffix;
    }

    public static function setupTenantDatabase($tenantDbName, $tenantUser): void
    {

        try{
            Artisan::call('tenant:database:create', ['name' => $tenantDbName]);
            Artisan::call('tenant:migrate', [
                'database' => $tenantDbName,
                '--path' => "database/migrations/tenant",
            ]);

            $originalDefaultConnection = Config::get('database.default');
            Config::set('database.default', $tenantDbName);

            Artisan::call('passport:install');
            Artisan::call('tenant:migrate', [
                'database' => $tenantDbName,
                '--path' => "database/migrations/tenant",
            ]);

            User::create($tenantUser);

            Config::set('database.default', $originalDefaultConnection);
        }catch (\Throwable $th) {
            self::rollbackChanges($tenantDbName);
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