<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantPrefixesSeeder extends Seeder
{
    public function run(): void
    {
        $procurementTypeId = DB::table('prefix_types')->where('prefix_type_name', 'Procurement')->value('id');
        $supplierTypeId = DB::table('prefix_types')->where('prefix_type_name', 'Supplier')->value('id');
        $assetTypeId = DB::table('prefix_types')->where('prefix_type_name', 'Asset')->value('id');

        DB::table('prefixes')->insert([
            [
                'prefix_type_id' => $procurementTypeId,
                'prefix' => 'PROC-',
                'next_id' => 5000,
            ],
            [
                'prefix_type_id' => $supplierTypeId,
                'prefix' => 'SUPP-',
                'next_id' => 4200,
            ],
            [
                'prefix_type_id' => $assetTypeId,
                'prefix' => 'ASSET-',
                'next_id' => 30050,
            ],
        ]);
    }
}