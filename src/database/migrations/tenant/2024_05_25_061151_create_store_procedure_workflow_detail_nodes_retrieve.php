<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_WORKFLOW_DETAILS_NODES(
                IN p_workflow_details_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS workflow_details_nodes_from_store_procedure;
            
                CREATE TEMP TABLE workflow_details_nodes_from_store_procedure AS
                SELECT * FROM
                    workflow_details 
                WHERE
                    workflow_details.id = p_workflow_details_id OR p_workflow_details_id IS NULL OR p_workflow_details_id = 0;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_WORKFLOW_DETAILS_NODES');
    }
};