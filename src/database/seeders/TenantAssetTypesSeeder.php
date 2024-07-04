<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantAssetTypesSeeder extends Seeder
{
    public function run(): void
    {
        $requestTypes = [
            ['asset_type' => 'Tangible', 'name' => 'Tangible assets', 'description' => 'These are any objects owned by a business that can generate revenue and have a physical form, such as cash, furniture, office equipment, machinery and so on'],
            ['asset_type' => 'Intangible', 'name' => 'Intangible assets', 'description' => 'These are assets that can generate revenue or goodwill and do not have a physical presence, such as branding, theme music, reputation, slogans and the like'],
            ['asset_type' => 'Operating', 'name' => 'Operating assets', 'description' => ' Anything that a business uses to generate revenue on a daily basis is considered to be an operating asset'],
            ['asset_type' => 'Non-operating', 'name' => 'Non-operating assets', 'description' => 'Anything that a business uses to generate revenue via means other than its core daily activities is designated as a non-operating asset'],
            ['asset_type' => 'Current', 'name' => 'Current assets', 'description' => 'Anything that is expected to be consumed or turned into cash within a short time frame is considered to be a current asset '],
            ['asset_type' => 'Fixed', 'name' => 'Fixed assets', 'description' => 'Anything that cannot be turned into cash within a short time frame and is subject to depreciation over time is considered to be a fixed asset '],
        ];

        DB::table('assets_types')->insert($requestTypes);
    }
}