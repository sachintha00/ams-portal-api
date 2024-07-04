<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $procedure = <<<SQL
                CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_WORKFLOW_REQUEST_REJECTED(
                    p_approver_user_id INT,
                    p_request_id INT,
                    p_workflow_node_id BIGINT,
                    p_asset_requisition_id TEXT,
                    p_approver_comment TEXT
                )
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    error_message TEXT;
                BEGIN
                    DROP TABLE IF EXISTS response;
                    CREATE TEMP TABLE response (
                        status TEXT,
                        message TEXT
                    );

                    IF p_approver_user_id IS NULL OR p_approver_user_id <= 0 THEN
                        INSERT INTO response (status, message)
                        VALUES ('ERROR', 'Approver User ID cannot be NULL or less than or equal to 0');
                        RETURN;
                    END IF;

                    IF p_request_id IS NULL OR p_request_id <= 0 THEN
                        INSERT INTO response (status, message)
                        VALUES ('ERROR', 'Request ID cannot be NULL or less than or equal to 0');
                        RETURN;
                    END IF;

                    IF p_asset_requisition_id IS NULL THEN
                        INSERT INTO response (status, message)
                        VALUES ('ERROR', ' Asset requisition ID cannot be NULL');
                        RETURN;
                    END IF;

                    IF p_workflow_node_id IS NULL THEN
                        INSERT INTO response (status, message)
                        VALUES ('ERROR', 'Workflow node ID cannot be NULL');
                        RETURN;
                    END IF;

                    UPDATE public.workflow_request_queue_details
                    SET approver_user_id = p_approver_user_id,
                        comment_for_action = p_approver_comment,
                        request_status_from_level = 'REJECTED',
                        updated_at = NOW()
                    WHERE request_id = p_request_id
                    AND workflow_node_id = p_workflow_node_id
                    AND request_status_from_level = 'PENDING';

                    UPDATE public.workflow_request_queues
                    SET workflow_request_status = 'REJECTED',
                        updated_at = NOW()
                    WHERE id = p_request_id
                    AND workflow_request_status = 'PENDING';

                    UPDATE public.asset_requisitions
                    SET requisition_status = 'REJECTED',
                        updated_at = NOW()
                    WHERE requisition_id = p_asset_requisition_id;

                    IF NOT FOUND THEN
                        TRUNCATE TABLE response;
                        error_message := 'No rows updated for the given conditions';
                        RAISE EXCEPTION 'No rows updated for the given conditions';
                    ELSE
                        TRUNCATE TABLE response;
                        INSERT INTO response (status, message)
                        VALUES ('SUCCESS', 'Rejected successfully');
                    END IF;

                EXCEPTION
                    WHEN OTHERS THEN
                        error_message := SQLERRM;
                        TRUNCATE TABLE response;
                        INSERT INTO response (status, message)
                        VALUES ('ERROR', 'Error during insert: ' || error_message);
                END;
                \$\$;
                SQL;
                
            // Execute the SQL statement
            DB::unprepared($procedure);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_WORKFLOW_REQUEST_REJECTED');
    }
};