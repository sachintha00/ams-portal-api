<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantRoleHasPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Retrieve the admin role
        $adminRole = Role::where('name', 'Super Admin')->first();

        // Retrieve all permissions
        $permissions = Permission::all();

        // Assign all permissions to the admin role
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }
    }
}