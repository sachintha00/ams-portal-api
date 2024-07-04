<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenantAssetsubcategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $VehicleId = DB::table('asset_categories')->where('name', 'Vehicle')->value('id');
        $MachineryId = DB::table('asset_categories')->where('name', 'Machinery')->value('id');
        $ComputerId = DB::table('asset_categories')->where('name', 'Computer & Hardware')->value('id');
        $ElectronicId = DB::table('asset_categories')->where('name', 'Electronic')->value('id');
        $currentTime = Carbon::now();

        DB::table('asset_sub_categories')->insert([
            [
                'asset_category_id' => $VehicleId,
                'name' => 'Bus',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $VehicleId,
                'name' => 'Bike',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $VehicleId,
                'name' => 'Lorry',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $MachineryId,
                'name' => 'Lacer cut machine',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $ComputerId,
                'name' => 'Laptop',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $ComputerId,
                'name' => 'Moniter',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $ElectronicId,
                'name' => 'Table Fan',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $ElectronicId,
                'name' => 'Tv',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'asset_category_id' => $ElectronicId,
                'name' => 'Ac Machine',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
        ]);
    }
}