<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $procedure = <<<SQL
                CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_GET_WORKFLOW_REQUEST_FIRST_APPROVER(
                    p_workflow_id BIGINT,
                    p_request_queue_id INT,
                    p_value INT,
                    p_start_node_id BIGINT DEFAULT 0,
                    p_combined_condition_result BOOLEAN DEFAULT FALSE
                )
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    current_node RECORD;
                    
                    condition JSON;
                    condition_sql TEXT;
                    combined_condition_sql TEXT := NULL;
                    condition_result BOOLEAN;

                    data_json JSONB;
                    
                    cursor_name CURSOR FOR
                        SELECT *
                FROM public.workflow_details wd
                WHERE 
                    wd.workflow_id = p_workflow_id 
                    AND wd.workflow_detail_parent_id = COALESCE(
                        (
                            CASE
                                WHEN wd.workflow_detail_type_id = 1 THEN p_start_node_id
                                ELSE (
                                    SELECT wrqd.workflow_node_id
                                    FROM public.workflow_request_queue_details wrqd
                                    JOIN public.workflow_request_queues wrq ON wrqd.request_id = wrq.id
                                    WHERE wrq.workflow_id = p_workflow_id
                                    AND wrqd.request_id = p_request_queue_id
                                    AND wrqd.request_status_from_level = 'APPROVED'
                                    ORDER BY wrqd.id DESC, wrqd.created_at DESC
                                    LIMIT 1
                                    )
                                END
                        ),
                        p_start_node_id
                    );

                BEGIN
                    DROP TABLE IF EXISTS workflow_request_process_data_from_store_procedure;
                    CREATE TEMP TABLE workflow_request_process_data_from_store_procedure (
                            status TEXT,
                            request_node_status TEXT,
                            behaviourType TEXT,
                            type TEXT,
                            data JSONB
                    );
                    INSERT INTO workflow_request_process_data_from_store_procedure (
                        status,
                        request_node_status,
                        behaviourType,
                        type,
                        data
                    ) VALUES (
                        'success',
                        'APPROVED',
                        4,
                        3,
                        '{}'::jsonb
                    );
                    OPEN cursor_name;
                    LOOP
                        FETCH cursor_name INTO current_node;
                        EXIT WHEN NOT FOUND;

                        
                        IF current_node.workflow_detail_type_id = 1 THEN
                            IF jsonb_typeof(current_node.workflow_detail_data_object::jsonb) = 'array' THEN
                                condition := current_node.workflow_detail_data_object->0;
                            ELSE
                                condition := current_node.workflow_detail_data_object;
                            END IF;
                    
                            IF (condition->>'condition')::BOOLEAN = p_combined_condition_result THEN
                                DROP TABLE IF EXISTS workflow_request_process_data_from_store_procedure;
                                CREATE TEMP TABLE workflow_request_process_data_from_store_procedure (
                                        status TEXT,
                                        request_node_status TEXT,
                                        behaviourType TEXT,
                                        type TEXT,
                                        data JSONB
                                );

                                data_json := (condition->'users')::jsonb;

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
                                        'PENDING', 0, current_node.workflow_detail_type_id, 2, '',  NOW(), NOW()
                                    );
                                END IF;
                                
                                INSERT INTO workflow_request_process_data_from_store_procedure (status, request_node_status, behaviourType, type, data) 
                                VALUES ('success', 'PENDING', condition->>'behaviourType', condition->>'type', data_json);
                                RETURN;
                            ELSEIF (condition->>'condition')::BOOLEAN THEN
                                RAISE INFO ' %',current_node;
                            END IF;
                        ELSIF current_node.workflow_detail_type_id = 2 THEN
                            combined_condition_sql := NULL;

                            FOR condition 
                                IN SELECT * FROM json_array_elements(current_node.workflow_detail_data_object->0->'conditions')
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
                        
                            EXECUTE 'SELECT (' || combined_condition_sql || ')::BOOLEAN' INTO p_combined_condition_result;
                            
                            RAISE NOTICE 'Combined Condition: %, Result: %', combined_condition_sql, p_combined_condition_result;
                            CALL traverse_workflow_tree(p_workflow_id, p_request_queue_id, p_value, current_node.id, p_combined_condition_result);
                            
                        ELSE
                            CALL traverse_workflow_tree(p_workflow_id, p_request_queue_id, p_value, current_node.id, p_combined_condition_result);
                        END IF;
                    
                    END LOOP;
                    CLOSE cursor_name;
                END;
                \$\$;
                SQL;
                
            // Execute the SQL statement
            DB::unprepared($procedure);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_GET_WORKFLOW_REQUEST_FIRST_APPROVER');
    }
};