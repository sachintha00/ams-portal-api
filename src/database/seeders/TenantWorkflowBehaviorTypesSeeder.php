<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantWorkflowBehaviorTypesSeeder extends Seeder
{
    public function run(): void
    {
        $workflowBehaviorTypes = [
            ['workflow_behavior_type' => 'Employee'],
            ['workflow_behavior_type' => 'Designation'],
            ['workflow_behavior_type' => 'Category'],
            ['workflow_behavior_type' => 'Approved'],
            ['workflow_behavior_type' => 'Condition'],
        ];

        DB::table('workflow_behavior_types')->insert($workflowBehaviorTypes);
    }
}