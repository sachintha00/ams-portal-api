<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantDBSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TenantAssetTypesSeeder::class);
        $this->call(TenantDesignationSeeder::class);
        $this->call(TenantDrawerDataSeeder::class);
        $this->call(TenantPrefixTypesSeeder::class);
        $this->call(TenantPrefixesSeeder::class);
        $this->call(TenantRequestTypesSeeder::class);
        $this->call(TenantWorkflowBehaviorTypesSeeder::class);
        $this->call(TenantWorkflowTypesSeeder::class);
        $this->call(TenantPermissionSeeder::class);
        $this->call(TenantRoleSeeder::class);
        $this->call(TenantRoleHasPermissionsSeeder::class);
        $this->call(TenantModelHasRolesSeeder::class);
        $this->call(TenantTbl_menuSeeder::class);
        $this->call(TenantAsset_categoriesSeeder::class);
        $this->call(TenantAssetsubcategoriesSeeder::class);
        $this->call(Tenantassest_requisition_availability_typeSeeder::class);
        $this->call(Tenantassest_requisition_period_typeSeeder::class);
        $this->call(Tenantassest_requisition_priority_typeSeeder::class);
    }
}