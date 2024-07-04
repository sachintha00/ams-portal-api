<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TenantPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = [
            [
                'name' => 'User Management',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => null,
            ],
            [
                'name' => 'Role',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 1,
            ],
            [
                'name' => 'create role',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 2,
            ],
            [
                'name' => 'update role',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 2,
            ],
            [
                'name' => 'delete role',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 2,
            ],
            [
                'name' => 'give permissions to role',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 2,
            ],
            [
                'name' => 'Users',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 1,
            ],
            [
                'name' => 'create user',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 7,
            ],
            [
                'name' => 'update user',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 7,
            ],
            [
                'name' => 'delete user',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 7,
            ],
            [
                'name' => 'user status change',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 7,
            ],
            [
                'name' => 'user password reset',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 7,
            ],
            [
                'name' => 'Config',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => null,
            ],
            [
                'name' => 'Organization',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 13,
            ],
            [
                'name' => 'Add Organization',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 14,
            ],
            [
                'name' => 'Update Organization',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 14,
            ],
            [
                'name' => 'Delete Organization',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 14,
            ],
            [
                'name' => 'Workflow',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 13,
            ],
            [
                'name' => 'Add Workflows',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 18,
            ],
            [
                'name' => 'Update Workflows',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 18,
            ],
            [
                'name' => 'Delete Workflows',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 18,
            ],
            [
                'name' => 'Workflow Nodes',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 18,
            ],
            [
                'name' => 'Add Workflow Node',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 18,
            ],
            [
                'name' => 'Update Workflow Node',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 18,
            ],
            [
                'name' => 'Delete Workflow Node',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 18,
            ],
            [
                'name' => 'Procurement Management',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => null,
            ],
            [
                'name' => 'Asset Requisitions',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 26,
            ],
            [
                'name' => 'Add Asset Requisitions',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 27,
            ],
            [
                'name' => 'Submit Asset Requisitions To Workflow',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 27,
            ],
            [
                'name' => 'Asset Requisitions More Details',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 27,
            ],
            [
                'name' => 'Procurement Initiate',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 26,
            ],
            [
                'name' => 'Add Procurement',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 32,
            ],
            [
                'name' => 'Add Suppliers Quotation',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 31,
            ],
            [
                'name' => 'Proceed Procurement',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 31,
            ],
            [
                'name' => 'Submit Procurement To Workflow',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 31,
            ],
            [
                'name' => 'Procurement Staff',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 26,
            ],
            [
                'name' => 'Add Procurement Staff',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 36,
            ],
            [
                'name' => 'Update Procurement Staff',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 36,
            ],
            [
                'name' => 'Delete Procurement Staff',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 36,
            ],
            [
                'name' => 'Supplier',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 26,
            ],
            [
                'name' => 'Add Supplier',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 40,
            ],
            [
                'name' => 'Update Supplier',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 40,
            ],
            [
                'name' => 'Delete Supplier',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 40,
            ],
            [
                'name' => 'Supplier Quotation',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 26,
            ],
            [
                'name' => 'Add Supplier Quotation',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 44,
            ],
            [
                'name' => 'View Supplier Quotation',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 44,
            ],
            [
                'name' => 'Update Supplier Quotation',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 44,
            ],
            [
                'name' => 'Delete Supplier Quotation',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 44,
            ],
            [
                'name' => 'Complete Quotation adding',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 44,
            ],
            [
                'name' => 'Assets Management',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 26,
            ],
            [
                'name' => 'Assets Record & Register ',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 50,
            ],
            [
                'name' => 'View Assets Details',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 50,
            ],
            [
                'name' => 'Update Assets Details',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 50,
            ],
            [
                'name' => 'Delete Assets Details',
                'guard_name' => 'api',
                'description' => 'test',
                'parent_id' => 50,
            ],
        ];

        // Seed multiple permission
        foreach ($permission as $Permission) {
            Permission::create($Permission);
        }
    }
}