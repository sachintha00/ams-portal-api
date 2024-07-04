<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Tenantassest_requisition_availability_typeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $availability_type = [
            [
                'name' => "Hire",
                'description' => "test",
            ],
            [
                'name' => "Rent",
                'description' => "test",
            ],
            [
                'name' => "Purchase",
                'description' => "test",
            ],
            [
                'name' => "Lease",
                'description' => "test",
            ],
        ];

        // Seed multiple period_type
        foreach ($availability_type as $Availability_type) {
            DB::table('assest_requisition_availability_type')->insert($Availability_type);
        }
    }
}