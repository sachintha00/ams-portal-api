<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_LIST_RELEVANT_WORKFLOWS(
                IN p_workflow_request_type_id INT DEFAULT NULL,
                IN p_user_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS all_relevant_workflows_from_store_procedure;
            
                IF p_workflow_request_type_id IS NOT NULL AND p_workflow_request_type_id < 0 THEN
                    RAISE EXCEPTION 'Invalid p_workflow_request_type_id: %', p_workflow_request_type_id;
                END IF;
            
                IF p_user_id IS NOT NULL AND p_user_id < 0 THEN
                    RAISE EXCEPTION 'Invalid p_user_id: %', p_user_id;
                END IF;
            
                CREATE TEMP TABLE all_relevant_workflows_from_store_procedure AS
                SELECT 
                    workflows.id AS workflow_id,
                    workflows.workflow_request_type_id,
                    workflows.workflow_name,
                    workflows.workflow_description,
                    workflows.workflow_status,
                    workflows.created_at,
                    workflows.updated_at
                FROM workflows
                WHERE (p_workflow_request_type_id IS NULL OR p_workflow_request_type_id = 0 OR workflows.workflow_request_type_id = p_workflow_request_type_id)
                    AND (workflows.workflow_status = true);
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_LIST_RELEVANT_WORKFLOWS');
    }
};