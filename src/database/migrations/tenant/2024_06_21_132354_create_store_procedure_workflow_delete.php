<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_DELETE_WORKFLOW(
                p_workflow_id bigint
            ) LANGUAGE plpgsql
            AS $$
            BEGIN
                IF p_workflow_id IS NULL OR p_workflow_id = 0 THEN
                    RAISE EXCEPTION 'Workflow ID cannot be null or zero';
                END IF;
            
                IF NOT EXISTS (SELECT 1 FROM workflows WHERE id = p_workflow_id) THEN
                    RAISE EXCEPTION 'Workflow with ID % does not exist', p_workflow_id;
                END IF;
            
                DELETE FROM workflow_details WHERE workflow_id = p_workflow_id;
                DELETE FROM workflows WHERE id = p_workflow_id;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_DELETE_WORKFLOW');
    }
};