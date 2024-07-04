<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $requestTypes = [
            ['designation' => 'Chief Executive Officer'],
            ['designation' => 'Chief Operating Officer'],
            ['designation' => 'Marketing Manager'],
            ['designation' => 'Humen Resource Manager'],
            ['designation' => 'Product Manager'],
        ];

        DB::table('designations')->insert($requestTypes);
    }
}