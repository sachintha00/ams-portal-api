<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Tenanttbl_menuSeeder extends Seeder
{
    public function run(): void
    {
        $tbl_menu_list = [
            [
                'permission_id' => 1,
                'parent_id' => null,
                'MenuName' => 'Dashboard',
                'description' => 'test',
                'MenuLink' => '/dashboard',
            ],
            [
                'permission_id' => 2,
                'parent_id' => null,
                'MenuName' => 'User Management',
                'description' => 'test',
                'MenuLink' => '#',
            ],
            [
                'permission_id' => 3,
                'parent_id' => 2,
                'MenuName' => 'Role',
                'description' => 'test',
                'MenuLink' => '/dashboard/Roles',
            ],
            [
                'permission_id' => 7,
                'parent_id' => 2,
                'MenuName' => 'Users',
                'description' => 'test',
                'MenuLink' => '/dashboard/users',
            ],
        ];
    
        foreach ($tbl_menu_list as $TBL_menu_list) {
            DB::table('tenanttbl_menu')->insert($TBL_menu_list);
        }
    }
}