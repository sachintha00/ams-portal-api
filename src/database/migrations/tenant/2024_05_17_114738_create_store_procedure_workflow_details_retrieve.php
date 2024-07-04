<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_WORKFLOW_DETAILS(
                IN p_workflow_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS workflow_details_from_store_procedure;
            
                CREATE TEMP TABLE workflow_details_from_store_procedure AS
                SELECT
                    w.id AS workflow_id,
                    wd.id AS workflow_detail_id,
                    wd.workflow_detail_parent_id AS workflow_detail_parent_id,
                    wd.workflow_detail_type_id,
                    wd.workflow_detail_behavior_type_id,
                    wd.workflow_detail_order,
                    wd.workflow_detail_level,
                    wd.workflow_detail_data_object::jsonb,
                    wrt.request_type AS workflow_request_type,
                    wt.workflow_type AS workflow_detail_type,
                    wbt.workflow_behavior_type AS workflow_detail_behavior_type
                FROM
                    workflows w
                INNER JOIN
                    workflow_details wd ON w.id = wd.workflow_id
                INNER JOIN
                    workflow_request_types wrt ON w.workflow_request_type_id = wrt.id
                INNER JOIN
                    workflow_types wt ON wd.workflow_detail_type_id = wt.id
                INNER JOIN
                    workflow_behavior_types wbt ON wd.workflow_detail_behavior_type_id = wbt.id
                WHERE
                    w.id = p_workflow_id OR p_workflow_id IS NULL OR p_workflow_id = 0
                GROUP BY
                    w.id, wd.id, wrt.request_type, wt.workflow_type, wbt.workflow_behavior_type;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_WORKFLOW_DETAILS');
    }
};