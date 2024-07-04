<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TenantModelHasRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Retrieve the user with ID 1
        $user = User::find(1);

        // Retrieve a role (e.g., 'admin' role)
        $adminRole = Role::where('name', 'Super Admin')->first();

        // Check if the user and role exist, then assign the role to the user
        if ($user && $adminRole) {
            $user->assignRole($adminRole);
        }
    }
}