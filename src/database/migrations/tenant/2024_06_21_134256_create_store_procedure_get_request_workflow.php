<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $procedure = <<<SQL
                CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_GET_REQUEST_WORKFLOW(
                    p_workflow_id BIGINT,
                    p_value INT
                )
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    current_node RECORD;
                    previous_node RECORD := NULL;

                    previous_node_id INT;

                    workflow_type TEXT;

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
                    DROP TABLE IF EXISTS workflow_request_process_path_data_from_store_procedure;
                    CREATE TEMP TABLE workflow_request_process_path_data_from_store_procedure (
                        status TEXT,
                        id BIGINT,
                        parent_id BIGINT,
                        type TEXT,
                        data JSONB
                    );
                    
                    OPEN workflow_path_cursor;
                    LOOP
                        FETCH workflow_path_cursor INTO current_node;
                        EXIT WHEN NOT FOUND;

                        IF previous_node IS NULL THEN
                            previous_node_id := 0;
                        ELSE
                            previous_node_id := previous_node.id;
                        END IF;

                        SELECT 
                            UPPER(wt.workflow_type) AS workflow_type
                        INTO 
                            workflow_type
                        FROM 
                            public.workflow_details wd
                        JOIN 
                            public.workflow_types wt 
                            ON wd.workflow_detail_type_id = wt.id
                        WHERE 
                            wd.id = current_node.id;

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
                                users_json := (condition->>'users')::jsonb;

                                FOR user_data IN SELECT * FROM jsonb_array_elements(users_json)
                                LOOP
                                    user_id := (user_data->>'id')::INT;
                                    
                                    SELECT * INTO existing_user FROM users WHERE id = user_id;
                    
                                    user_object := jsonb_build_object(
                                                        'id', existing_user.id, 
                                                        'name', existing_user.name,
                                                        'profile_image', existing_user.profie_image
                                                    );

                                    user_array := array_append(user_array, user_object);
                                END LOOP;
                                
                                updated_users_json := jsonb_agg(user_array)->0;
                                updated_workflow_node := jsonb_set(condition::jsonb, '{users}', updated_users_json);

                                INSERT INTO workflow_request_process_path_data_from_store_procedure (
                                    status,
                                    id,
                                    parent_id,
                                    type,
                                    data
                                ) VALUES (
                                    'success',
                                    current_node.id,
                                    current_node.workflow_detail_parent_id,
                                    workflow_type::TEXT,
                                    updated_workflow_node::jsonb
                                );

                                RAISE NOTICE 'Workflow Node %', current_node;
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

                            INSERT INTO workflow_request_process_path_data_from_store_procedure (
                                status,
                                id,
                                parent_id,
                                type,
                                data
                            ) VALUES (
                                'success',
                                current_node.id,
                                current_node.workflow_detail_parent_id,
                                workflow_type::TEXT,
                                current_node.workflow_detail_data_object::jsonb
                            );

                            previous_node := current_node;
                            RAISE INFO 'current_node %', current_node;
                            RAISE INFO 'Condition %', combined_condition_result;
                        ELSEIF current_node.workflow_detail_type_id = 3 THEN
                            IF current_node.workflow_detail_parent_id = previous_node_id THEN
                                INSERT INTO workflow_request_process_path_data_from_store_procedure (
                                    status,
                                    id,
                                    parent_id,
                                    type,
                                    data
                                ) VALUES (
                                    'success',
                                    current_node.id,
                                    current_node.workflow_detail_parent_id,
                                    workflow_type::TEXT,
                                    current_node.workflow_detail_data_object::jsonb
                                );

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
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_GET_REQUEST_WORKFLOW');
    }
};