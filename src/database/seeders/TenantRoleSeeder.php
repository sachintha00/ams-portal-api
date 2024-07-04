<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TenantRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Role = [
            [
                'name' => 'Super Admin',
                'guard_name' => 'api',
                'description' => 'test',
            ],
        ];

        // Seed multiple permission
        foreach ($Role as $Role) {
            Role::create($Role);
        }
    }
}