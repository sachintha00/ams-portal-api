<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantDBSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TenantPermissionSeeder::class);
        $this->call(TenantRequestTypesSeeder::class);
        // $this->call(Tenanttbl_menuSeeder::class);
        $this->call(TenantWorkflowBehaviorTypesSeeder::class);
        $this->call(TenantWorkflowTypesSeeder::class);
    }
}