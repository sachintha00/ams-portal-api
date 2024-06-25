<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $procedure = <<<SQL
                CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_WORKFLOW_PROCESS(
                    p_workflow_id BIGINT,
                    p_request_queue_id BIGINT,
                    p_value INT,
                    p_designation_user_id BIGINT DEFAULT NULL
                )
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    current_node RECORD;
                    previous_node RECORD := NULL;

                    previous_node_id INT;

                    workflow_node_id_from_request_queue_details BIGINT;

                    condition JSON;
                    condition_sql TEXT;
                    combined_condition_sql TEXT := NULL;
                    condition_result BOOLEAN;
                    combined_condition_result BOOLEAN;

                    users_json JSONB;
                    user_data JSONB;
                    updated_users_json JSONB;
                    user_object JSONB;
                    user_array JSONB[] := '{}';
                    updated_workflow_node JSONB;
                    user_id INT;
                    existing_user RECORD;

                    workflow_path_cursor CURSOR FOR
                        SELECT *
                        FROM public.workflow_details wd
                        WHERE wd.workflow_id = p_workflow_id
                        ORDER BY wd.created_at;

                BEGIN
                    workflow_node_id_from_request_queue_details := COALESCE(
                        (
                            SELECT wrqd.workflow_node_id
                            FROM public.workflow_request_queue_details wrqd
                            JOIN public.workflow_request_queues wrq ON wrqd.request_id = wrq.id
                            WHERE wrq.workflow_id = p_workflow_id
                            AND wrqd.request_id = p_request_queue_id
                            AND wrqd.request_status_from_level = 'APPROVED'
                            ORDER BY wrqd.id DESC, wrqd.created_at DESC
                            LIMIT 1
                        ),
                        0
                    );
                    
                    OPEN workflow_path_cursor;
                    LOOP
                        FETCH workflow_path_cursor INTO current_node;
                        EXIT WHEN NOT FOUND;

                        IF previous_node IS NULL THEN
                            previous_node_id := COALESCE(
                                workflow_node_id_from_request_queue_details, 0
                            );
                        ELSE
                            previous_node_id := previous_node.id;
                        END IF;

                        IF current_node.workflow_detail_type_id = 1 THEN
                            IF jsonb_typeof(current_node.workflow_detail_data_object::jsonb) = 'array' THEN
                                condition := current_node.workflow_detail_data_object->0;
                            ELSE
                                condition := current_node.workflow_detail_data_object;
                            END IF;


                            IF ((condition->>'condition')::BOOLEAN = combined_condition_result
                                AND current_node.workflow_detail_parent_id = previous_node_id)
                                OR ((condition->>'isConditionResult')::BOOLEAN = FALSE::BOOLEAN
                                AND current_node.workflow_detail_parent_id = previous_node_id)
                            THEN
                                IF condition->>'behaviourType' = 'EMPLOYEE' THEN
                                    IF workflow_node_id_from_request_queue_details = 0 AND current_node.workflow_detail_parent_id = previous_node_id THEN
                                        IF NOT EXISTS (
                                            SELECT 1
                                            FROM public.workflow_request_queue_details
                                            WHERE request_id = p_request_queue_id AND workflow_node_id = current_node.id
                                        ) THEN
                                            INSERT INTO public.workflow_request_queue_details (
                                                request_id, workflow_node_id, workflow_level, request_status_from_level, 
                                                workflow_auth_order, workflow_type, comment_for_action, 
                                                created_at, updated_at
                                            ) VALUES 
                                            ( p_request_queue_id, current_node.id, current_node.workflow_detail_level, 
                                                'PENDING', 0, current_node.workflow_detail_type_id, '',  NOW(), NOW()
                                            );
                                        END IF;
                                        RAISE NOTICE 'Workflow Node %', current_node;
                                        RETURN;
                                    ELSEIF NOT workflow_node_id_from_request_queue_details = 0 AND current_node.workflow_detail_parent_id = workflow_node_id_from_request_queue_details THEN
                                        IF NOT EXISTS (
                                            SELECT 1
                                            FROM public.workflow_request_queue_details
                                            WHERE request_id = p_request_queue_id AND workflow_node_id = current_node.id
                                        ) THEN
                                            INSERT INTO public.workflow_request_queue_details (
                                                request_id, workflow_node_id, workflow_level, request_status_from_level, 
                                                workflow_auth_order, workflow_type, comment_for_action, 
                                                created_at, updated_at
                                            ) VALUES 
                                            ( p_request_queue_id, current_node.id, current_node.workflow_detail_level, 
                                                'PENDING', 0, current_node.workflow_detail_type_id, '',  NOW(), NOW()
                                            );
                                        END IF;
                                        RAISE NOTICE 'Workflow Node %', current_node;
                                        RETURN;
                                    ELSEIF current_node.workflow_detail_parent_id = previous_node_id AND current_node.workflow_detail_parent_id = workflow_node_id_from_request_queue_details AND (condition->>'condition')::BOOLEAN = combined_condition_result THEN
                                        RAISE NOTICE 'previous_node_id %', previous_node_id;
                                        RAISE NOTICE 'current_node.workflow_detail_parent_id %', current_node.workflow_detail_parent_id;
                                        RAISE NOTICE 'workflow_node_id_from_request_queue_details %', workflow_node_id_from_request_queue_details;
                                        RAISE NOTICE 'Workflow Node %', current_node;
                                        RETURN;
                                    END IF;
                                ELSEIF condition->>'behaviourType' = 'DESIGNATION' THEN
                                    IF workflow_node_id_from_request_queue_details = 0 AND current_node.workflow_detail_parent_id = previous_node_id THEN
                                        IF NOT EXISTS (
                                            SELECT 1
                                            FROM public.workflow_request_queue_details
                                            WHERE request_id = p_request_queue_id AND workflow_node_id = current_node.id
                                        ) THEN
                                            INSERT INTO public.workflow_request_queue_details (
                                                request_id, workflow_node_id, workflow_level, request_status_from_level, 
                                                workflow_auth_order, workflow_type, approver_user_id, comment_for_action, 
                                                created_at, updated_at
                                            ) VALUES 
                                            ( p_request_queue_id, current_node.id, current_node.workflow_detail_level, 
                                                'PENDING', 0, current_node.workflow_detail_type_id, p_designation_user_id, '',  NOW(), NOW()
                                            );
                                        END IF;
                                        RAISE NOTICE 'Workflow Node %', current_node;
                                        RETURN;
                                    ELSEIF NOT workflow_node_id_from_request_queue_details = 0 AND current_node.workflow_detail_parent_id = workflow_node_id_from_request_queue_details THEN
                                        IF NOT EXISTS (
                                            SELECT 1
                                            FROM public.workflow_request_queue_details
                                            WHERE request_id = p_request_queue_id AND workflow_node_id = current_node.id
                                        ) THEN
                                            INSERT INTO public.workflow_request_queue_details (
                                                request_id, workflow_node_id, workflow_level, request_status_from_level, 
                                                workflow_auth_order, workflow_type, approver_user_id, comment_for_action, 
                                                created_at, updated_at
                                            ) VALUES 
                                            ( p_request_queue_id, current_node.id, current_node.workflow_detail_level, 
                                                'PENDING', 0, current_node.workflow_detail_type_id, p_designation_user_id, '',  NOW(), NOW()
                                            );
                                        END IF;
                                        RAISE NOTICE 'Workflow Node %', current_node;
                                        RETURN;
                                    ELSEIF current_node.workflow_detail_parent_id = previous_node_id AND current_node.workflow_detail_parent_id = workflow_node_id_from_request_queue_details AND (condition->>'condition')::BOOLEAN = combined_condition_result THEN
                                        RAISE NOTICE 'previous_node_id %', previous_node_id;
                                        RAISE NOTICE 'current_node.workflow_detail_parent_id %', current_node.workflow_detail_parent_id;
                                        RAISE NOTICE 'workflow_node_id_from_request_queue_details %', workflow_node_id_from_request_queue_details;
                                        RAISE NOTICE 'Workflow Node %', current_node;
                                        RETURN;
                                    END IF;
                                END IF;
                                previous_node := current_node;
                            END IF;
                        ELSEIF current_node.workflow_detail_type_id = 2 THEN
                            combined_condition_sql := NULL;

                            FOR condition IN SELECT * FROM json_array_elements(current_node.workflow_detail_data_object->0->'conditions')
                            LOOP
                                condition_sql := condition::TEXT;
                                condition_sql := trim(both '"' from condition_sql);

                                condition_sql := replace(condition_sql, 'and', ' AND ');
                                condition_sql := replace(condition_sql, 'or', ' OR ');
                                condition_sql := replace(condition_sql, '==', '=');
                                condition_sql := replace(condition_sql, '''value''', p_value::TEXT);

                                IF combined_condition_sql IS NULL THEN
                                    combined_condition_sql := condition_sql;
                                ELSE
                                    combined_condition_sql := combined_condition_sql || ' AND ' || condition_sql;
                                END IF;
                            END LOOP;

                            EXECUTE 'SELECT (' || combined_condition_sql || ')::BOOLEAN' INTO combined_condition_result;

                            previous_node := current_node;
                            RAISE INFO 'Condition Node: %', current_node;
                            RAISE INFO 'Condition %', combined_condition_result;
                        ELSEIF current_node.workflow_detail_type_id = 3 THEN
                            IF current_node.workflow_detail_parent_id = previous_node_id THEN
                                UPDATE public.workflow_request_queues
                                SET workflow_request_status = 'APPROVED'
                                WHERE id = p_request_queue_id;
                                RAISE INFO 'Approved %', current_node;
                                RETURN;
                            END IF;
                        END IF;

                    END LOOP;
                    CLOSE workflow_path_cursor;
                END;
                \$\$;
                SQL;
                
            // Execute the SQL statement
            DB::unprepared($procedure);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_WORKFLOW_PROCESS');
    }
};