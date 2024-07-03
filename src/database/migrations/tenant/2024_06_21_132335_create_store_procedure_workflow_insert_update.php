<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_INSERT_OR_UPDATE_WORKFLOW(
                p_workflow_request_type_id bigint,
                p_workflow_name text,
                p_workflow_description text,
                OUT p_inserted_or_updated_workflow_id bigint,
                p_workflow_id bigint DEFAULT NULL,
                p_workflow_status boolean DEFAULT true
            ) LANGUAGE plpgsql
            AS $$
            BEGIN
                IF p_workflow_request_type_id IS NULL OR p_workflow_request_type_id = 0 THEN
                    RAISE EXCEPTION 'Workflow request type ID cannot be null or zero';
                END IF;
                
                IF p_workflow_name IS NULL OR p_workflow_name = '' THEN
                    RAISE EXCEPTION 'Workflow name cannot be null or empty';
                END IF;
            
                IF p_workflow_description IS NULL THEN
                    RAISE EXCEPTION 'Workflow description cannot be null';
                END IF;
            
                IF p_workflow_id IS NULL OR p_workflow_id = 0 THEN

                    INSERT INTO workflows (workflow_request_type_id, workflow_name, workflow_description, workflow_status, created_at, updated_at)
                    VALUES (p_workflow_request_type_id, p_workflow_name, p_workflow_description, p_workflow_status, NOW(), NOW())
                    RETURNING id INTO p_inserted_or_updated_workflow_id;
                ELSE

                    UPDATE workflows
                    SET 
                        workflow_request_type_id = p_workflow_request_type_id,
                        workflow_name = p_workflow_name,
                        workflow_description = p_workflow_description,
                        workflow_status = p_workflow_status,
                        updated_at = NOW()
                    WHERE id = p_workflow_id;
                    
                    p_inserted_or_updated_workflow_id := p_workflow_id;
                END IF;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_INSERT_OR_UPDATE_WORKFLOW');
    }
};