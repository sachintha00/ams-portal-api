<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantPrefixTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('prefix_types')->insert([
            [
                'prefix_type_name' => 'Procurement',
                'description' => 'Prefix for procurement process'
            ],
            [
                'prefix_type_name' => 'Supplier',
                'description' => 'Prefix for supplier requisitions'
            ],
            [
                'prefix_type_name' => 'Asset',
                'description' => 'Prefix for asset requisitions'
            ],
        ]);
    }
}