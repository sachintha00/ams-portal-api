<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_WORKFLOW_REQUEST_SUBMIT(
                p_user_id INT,
                p_workflow_request_type_id INT,
                p_workflow_id INT,
                p_asset_requisition_id TEXT,
                p_requisition_data_object JSONB,
                p_request_comment TEXT DEFAULT NULL
            )
            AS $$
            DECLARE
                error_message TEXT;
                request_id BIGINT;
            BEGIN
                DROP TABLE IF EXISTS response;
                CREATE TEMP TABLE response (
                    status TEXT,
                    message TEXT,
                    request_id BIGINT DEFAULT 0
                );

                IF p_user_id IS NULL OR p_user_id <= 0 THEN
                    INSERT INTO response (status, message)
                    VALUES ('ERROR', 'User ID cannot be NULL or less than or equal to 0');
                    RETURN;
                END IF;

                IF p_workflow_request_type_id IS NULL OR p_workflow_request_type_id <= 0 THEN
                    INSERT INTO response (status, message)
                    VALUES ('ERROR', 'Workflow request type ID cannot be NULL or less than or equal to 0');
                    RETURN;
                END IF;

                IF p_workflow_id IS NULL OR p_workflow_id <= 0 THEN
                    INSERT INTO response (status, message)
                    VALUES ('ERROR', 'Workflow ID cannot be NULL or less than or equal to 0');
                    RETURN;
                END IF;

                IF p_requisition_data_object IS NULL THEN
                    INSERT INTO response (status, message)
                    VALUES ('ERROR', 'Requisition data object cannot be NULL');
                    RETURN;
                END IF;

                IF p_request_comment IS NOT NULL AND LENGTH(p_request_comment) > 500 THEN
                    INSERT INTO response (status, message)
                    VALUES ('ERROR', 'Request comment exceeds the maximum length of 500 characters');
                    RETURN;
                END IF;

                BEGIN
                    INSERT INTO workflow_request_queues(
                        user_id, 
                        workflow_request_type, 
                        workflow_id, 
                        requisition_data_object, 
                        workflow_request_status
                    )
                    VALUES ( 
                        p_user_id, 
                        p_workflow_request_type_id, 
                        p_workflow_id, 
                        p_requisition_data_object, 
                        'PENDING'
                    )RETURNING id INTO request_id;

                    RAISE INFO 'Test %',request_id;
                    
                    INSERT INTO response (status, message, request_id)
                    VALUES ('SUCCESS', 'Data inserted successfully', request_id);
                EXCEPTION
                    WHEN OTHERS THEN
                        error_message := SQLERRM;
                        INSERT INTO response (status, message)
                        VALUES ('ERROR', 'Error during insert: ' || error_message);
                END;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_WORKFLOW_REQUEST_SUBMIT');
    }
};