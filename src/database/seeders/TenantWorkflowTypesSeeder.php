<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantWorkflowTypesSeeder extends Seeder
{
    public function run(): void
    {
        $workflowTypes = [
            ['workflow_type' => 'Workflow'],
            ['workflow_type' => 'Condition'],
            ['workflow_type' => 'Approved'],
        ];

        DB::table('workflow_types')->insert($workflowTypes);
    }
}