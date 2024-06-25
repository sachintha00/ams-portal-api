<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_WORKFLOW_ALERT_DATA_RELEVANT_APPROVER(
                p_user_id BIGINT
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                workflow_data RECORD;

                previous_user_id BIGINT;

                workflow_data_obj JSONB;
                user_data JSONB;

                previous_user_data JSONB;
                requested_user_data JSONB;

                workflow_request_type TEXT;

                workflows_data_cursor CURSOR FOR
                    SELECT wrq.id AS workflow_request_id, 
                        wrq.user_id, 
                        wrq.workflow_request_type, 
                        wrq.workflow_id, 
                        wrq.requisition_data_object, 
                        wrq.workflow_request_status, 
                        wrq.created_at AS request_created_at, 
                        wrq.updated_at AS request_updated_at,
                        wrqd.id AS workflow_request_detail_id, 
                        wrqd.request_id, 
                        wrqd.workflow_node_id, 
                        wrqd.workflow_level, 
                        wrqd.request_status_from_level, 
                        wrqd.workflow_auth_order, 
                        wrqd.workflow_type, 
                        wrqd.approver_user_id, 
                        wrqd.comment_for_action, 
                        wrqd.created_at AS detail_created_at, 
                        wrqd.updated_at AS detail_updated_at,
                        wd.id AS workflow_detail_id,
                        wd.workflow_detail_parent_id, 
                        wd.workflow_id AS wd_workflow_id, 
                        wd.workflow_detail_type_id, 
                        wd.workflow_detail_behavior_type_id, 
                        wd.workflow_detail_order, 
                        wd.workflow_detail_level, 
                        wd.workflow_detail_data_object, 
                        wd.created_at AS wd_created_at, 
                        wd.updated_at AS wd_updated_at
                    FROM public.workflow_request_queues wrq
                    JOIN public.workflow_request_queue_details wrqd ON wrq.id = wrqd.request_id
                    JOIN public.workflow_details wd ON wrqd.workflow_node_id = wd.id
                    WHERE wrqd.request_status_from_level = 'PENDING'
                    ORDER BY wrq.created_at, wrqd.created_at;

            BEGIN
                DROP TABLE IF EXISTS workflow_alert_data_from_store_procedure;
                CREATE TEMP TABLE workflow_alert_data_from_store_procedure (
                    id BIGSERIAL PRIMARY KEY, 
                    requested_user JSONB,
                    workflow_request_type TEXT,
                    workflow_id BIGINT,
                    requested_data_obj JSONB,
                    request_status TEXT,
                    previous_approver_details JSONB
                );
                OPEN workflows_data_cursor;
                LOOP
                    FETCH workflows_data_cursor INTO workflow_data;
                    EXIT WHEN NOT FOUND;

                    IF workflow_data.workflow_detail_behavior_type_id = 1 THEN
                        IF jsonb_typeof(workflow_data.workflow_detail_data_object::jsonb) = 'array' THEN
                            workflow_data_obj := workflow_data.workflow_detail_data_object->0;
                        ELSE
                            workflow_data_obj := workflow_data.workflow_detail_data_object;
                        END IF;
                
                        FOR user_data IN SELECT * FROM jsonb_array_elements((workflow_data_obj->>'users')::jsonb)
                        LOOP
                            IF (user_data->>'id')::BIGINT = p_user_id THEN

                                SELECT approver_user_id INTO previous_user_id FROM workflow_request_queue_details
                                WHERE id = (
                                    SELECT MAX(id) FROM workflow_request_queue_details WHERE id < workflow_data.workflow_request_detail_id AND workflow_node_id < workflow_data.workflow_node_id
                                );

                                SELECT json_build_object(
                                    'id', u.id,
                                    'name', u.name,
                                    'email', u.email,
                                    'profile_image', u.profie_image
                                ) INTO previous_user_data
                                FROM users u
                                WHERE u.id = previous_user_id::BIGINT;

                                SELECT json_build_object(
                                    'id', u.id,
                                    'name', u.name,
                                    'email', u.email,
                                    'profile_image', u.profie_image
                                ) INTO requested_user_data
                                FROM users u
                                WHERE u.id = (user_data->>'id')::BIGINT;

                                SELECT request_type INTO workflow_request_type FROM public.workflow_request_types
                                WHERE id = workflow_data.workflow_request_type;

                                INSERT INTO workflow_alert_data_from_store_procedure (
                                    requested_user,
                                    workflow_request_type,
                                    workflow_id,
                                    requested_data_obj,
                                    request_status,
                                    previous_approver_details
                                ) VALUES (
                                    requested_user_data::JSONB,
                                    workflow_request_type::TEXT,
                                    workflow_data.workflow_detail_id::BIGINT,
                                    workflow_data.requisition_data_object::JSONB,
                                    workflow_data.request_status_from_level::TEXT,
                                    previous_user_data::JSONB
                                );
                            END IF;
                        END LOOP;
                    END IF;

                END LOOP;
                CLOSE workflows_data_cursor;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_WORKFLOW_ALERT_DATA_RELEVANT_APPROVER');
    }
};