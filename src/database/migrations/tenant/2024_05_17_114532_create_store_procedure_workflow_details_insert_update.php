<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_INSERT_OR_UPDATE_WORKFLOW_DETAILS(
                p_workflow_id bigint,
                p_workflow_detail_parent_id bigint,
                p_workflow_detail_type_id bigint,
                p_workflow_detail_behavior_type_id bigint,
                p_workflow_detail_order integer,
                p_workflow_detail_level integer,
                p_workflow_detail_data_object json,
                p_workflow_detail_id bigint DEFAULT NULL
            ) LANGUAGE plpgsql
            AS $$
            BEGIN
                IF p_workflow_id IS NULL OR p_workflow_id = 0 THEN
                    RAISE EXCEPTION 'Workflow ID cannot be null or zero';
                END IF;
            
                IF p_workflow_detail_type_id IS NULL OR p_workflow_detail_type_id = 0 THEN
                    RAISE EXCEPTION 'Workflow detail type ID cannot be null or zero';
                END IF;
            
                IF p_workflow_detail_behavior_type_id IS NULL OR p_workflow_detail_behavior_type_id = 0 THEN
                    RAISE EXCEPTION 'Workflow detail behavior type ID cannot be null or zero';
                END IF;
            
                IF p_workflow_detail_order IS NULL THEN
                    RAISE EXCEPTION 'Workflow detail order cannot be null';
                END IF;
            
                IF p_workflow_detail_level IS NULL THEN
                    RAISE EXCEPTION 'Workflow detail level cannot be null';
                END IF;
            
                IF p_workflow_detail_data_object IS NULL THEN
                    RAISE EXCEPTION 'Workflow detail data object cannot be null';
                END IF;
            
                IF p_workflow_detail_id IS NULL OR p_workflow_detail_id = 0 THEN
                    INSERT INTO workflow_details (workflow_id, workflow_detail_parent_id, workflow_detail_type_id, workflow_detail_behavior_type_id, workflow_detail_order, workflow_detail_level, workflow_detail_data_object, created_at, updated_at)
                    VALUES (p_workflow_id, p_workflow_detail_parent_id, p_workflow_detail_type_id, p_workflow_detail_behavior_type_id, p_workflow_detail_order, p_workflow_detail_level, p_workflow_detail_data_object, NOW(), NOW());
                ELSE
                    UPDATE workflow_details
                    SET 
                        workflow_detail_parent_id = p_workflow_detail_parent_id,
                        workflow_detail_type_id = p_workflow_detail_type_id,
                        workflow_detail_behavior_type_id = p_workflow_detail_behavior_type_id,
                        workflow_detail_order = p_workflow_detail_order,
                        workflow_detail_level = p_workflow_detail_level,
                        workflow_detail_data_object = p_workflow_detail_data_object,
                        updated_at = NOW()
                    WHERE id = p_workflow_detail_id AND workflow_id = p_workflow_id;
                    
                    IF NOT FOUND THEN
                        INSERT INTO workflow_details (workflow_id, workflow_detail_parent_id, workflow_detail_type_id, workflow_detail_behavior_type_id, workflow_detail_order, workflow_detail_level, workflow_detail_data_object, created_at, updated_at)
                        VALUES (p_workflow_id, p_workflow_detail_parent_id, p_workflow_detail_type_id, p_workflow_detail_behavior_type_id, p_workflow_detail_order, p_workflow_detail_level, p_workflow_detail_data_object, NOW(), NOW());
                    END IF;
                END IF;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_INSERT_OR_UPDATE_WORKFLOW_DETAILS');
    }
};