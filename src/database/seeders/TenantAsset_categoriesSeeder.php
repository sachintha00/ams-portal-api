<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantAsset_categoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('asset_categories')->insert([
            [
                'name' => 'Vehicle',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
            ],
            [
                'name' => 'Machinery',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
            ],
            [
                'name' => 'Computer & Hardware',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
            ],
            [
                'name' => 'Electronic',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
            ],
        ]);
    }
}