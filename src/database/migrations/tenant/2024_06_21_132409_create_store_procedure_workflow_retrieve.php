<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_WORKFLOW(
                IN p_workflow_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS workflow_from_store_procedure;
            
                CREATE TEMP TABLE workflow_from_store_procedure AS
                SELECT * FROM
                    workflows 
                WHERE
                    workflows.id = p_workflow_id OR p_workflow_id IS NULL OR p_workflow_id = 0;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_WORKFLOW');
    }
};