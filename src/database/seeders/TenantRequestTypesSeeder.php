<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantRequestTypesSeeder extends Seeder
{
    public function run(): void
    {
        $requestTypes = [
            ['request_type' => 'Asset Requisition'],
            ['request_type' => 'Supplier Registration'],
        ];

        DB::table('workflow_request_types')->insert($requestTypes);
    }
}