<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_LIST_WORKFLOW_REQUEST_TYPES(
                IN p_workflow_request_type_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS workflow_request_types_from_store_procedure;
            
                IF p_workflow_request_type_id IS NOT NULL AND p_workflow_request_type_id < 0 THEN
                    RAISE EXCEPTION 'Invalid p_workflow_request_type_id: %', p_workflow_request_type_id;
                END IF;
            
                CREATE TEMP TABLE workflow_request_types_from_store_procedure AS
                SELECT * FROM workflow_request_types
                WHERE
                    (p_workflow_request_type_id IS NULL)
                    OR
                    (p_workflow_request_type_id = 0)
                    OR
                    (workflow_request_types.id = p_workflow_request_type_id);
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_LIST_WORKFLOW_REQUEST_TYPES');
    }
};