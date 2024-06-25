<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Helpers\DBSwitchHelper;
use Illuminate\Support\Facades\Config;
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
        try {
            Artisan::call('tenant:database:create', ['name' => $tenantDbName]);
            Artisan::call('tenant:migrate', [
                'database' => $tenantDbName,
                '--path' => "database/migrations/tenant",
            ]);


            DBSwitchHelper::switch($tenantDbName);

            DB::purge($tenantDbName);
            DB::reconnect($tenantDbName);

            $defaultConnection = DB::getDefaultConnection();
            DB::setDefaultConnection($tenantDbName);

            Artisan::call('db:seed', [
                '--class' => 'TenantDBSeeder',
                '--database' => $tenantDbName
            ]);

            User::on($tenantDbName)->create($tenantUser);

            DB::setDefaultConnection($defaultConnection);

            DB::disconnect($tenantDbName);

        } catch (\Throwable $th) {
            DB::setDefaultConnection($defaultConnection);
            DB::disconnect($tenantDbName); 

            self::rollbackChanges($tenantDbName);
            throw $th;
        }
    }

    public static function rollbackChanges($tenantDbName): void
    {
        try {
            DB::statement("SELECT pg_terminate_backend(pg_stat_activity.pid)
                            FROM pg_stat_activity
                            WHERE pg_stat_activity.datname = ?
                            AND pid <> pg_backend_pid()", [$tenantDbName]);

            self::dropTenantDatabase($tenantDbName);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function dropTenantDatabase($tenantDbName): void
    {
        DB::statement("DROP DATABASE IF EXISTS \"$tenantDbName\"");
    }
}