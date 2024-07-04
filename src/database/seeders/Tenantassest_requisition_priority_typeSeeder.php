<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\assest_requisition_availability_type;
use Illuminate\Support\Facades\DB;

class Tenantassest_requisition_priority_typeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $priority_type = [
            [
                'name' => "Normal",
                'description' => "test",
            ],
            [
                'name' => "Moderate",
                'description' => "test",
            ],
            [
                'name' => "High",
                'description' => "test",
            ],
            [
                'name' => "Highest",
                'description' => "test",
            ],
        ];

        foreach ($priority_type as $priority_type) {
            DB::table('assest_requisition_priority_type')->insert($priority_type);
        }
    }
}