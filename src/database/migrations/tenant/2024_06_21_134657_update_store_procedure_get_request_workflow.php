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
                    p_value INT,
                    p_node_id BIGINT DEFAULT NULL
                )
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    current_node RECORD;
                    previous_node RECORD := NULL;
                    last_approved_node RECORD := NULL;

                    previous_node_id INT;

                    workflow_type TEXT;

                    final_data_obj JSONB;

                    condition JSON;
                    condition_sql TEXT;
                    combined_condition_sql TEXT := NULL;
                    combined_condition_result BOOLEAN;

                    users_json JSONB;
                    updated_users_json JSONB;
                    updated_workflow_node JSONB;

                    user_ids INT[];
                    user_details JSONB[];
                    user_details_final JSONB := '[]'::JSONB;
                    user_detail JSONB;

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

                        IF jsonb_typeof(current_node.workflow_detail_data_object::jsonb) = 'array' THEN
                            condition := current_node.workflow_detail_data_object->0;
                        ELSE
                            condition := current_node.workflow_detail_data_object;
                        END IF;

                        IF current_node.workflow_detail_type_id = 1 THEN
                            IF ((condition->>'condition')::BOOLEAN = combined_condition_result
                                    AND current_node.workflow_detail_parent_id = previous_node_id)
                                    OR ((condition->>'isConditionResult')::BOOLEAN = FALSE::BOOLEAN
                                    AND current_node.workflow_detail_parent_id = previous_node_id)
                                THEN

                                    IF condition->>'behaviourType' = 'EMPLOYEE' THEN
                                        users_json := (condition->>'users')::jsonb;

                                        SELECT ARRAY(SELECT (jsonb_array_elements(users_json)->>'id')::INT)
                                        INTO user_ids;
                                    
                                        SELECT ARRAY(
                                            SELECT jsonb_build_object('name', u.name, 'address', u.address)
                                            FROM users u
                                            WHERE u.id = ANY(user_ids)
                                        )
                                        INTO user_details;
                                    
                                        user_details_final := '[]'::JSONB;
                                    
                                        FOR i IN 1..array_length(user_details, 1) LOOP
                                            user_detail := user_details[i];
                                            IF NOT EXISTS (
                                                SELECT 1
                                                FROM jsonb_array_elements(user_details_final) AS elem
                                                WHERE (elem->>'name')::TEXT = (user_detail->>'name')::TEXT
                                                AND (elem->>'address')::TEXT = (user_detail->>'address')::TEXT
                                            ) THEN
                                                user_details_final := user_details_final || jsonb_build_array(user_detail);
                                            END IF;
                                        END LOOP;


                                        updated_users_json := jsonb_agg(user_details_final)->0;
                                        updated_workflow_node := jsonb_set(condition::jsonb, '{users}', updated_users_json);

                                        final_data_obj := updated_workflow_node;
                                        -- RAISE INFO '%', final_data_obj;
                                    ELSEIF condition->>'behaviourType' = 'DESIGNATION' THEN
                                        final_data_obj := current_node.workflow_detail_data_object->0;
                                    END IF;

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
                                            final_data_obj
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
                                RAISE NOTICE 'Approved %', current_node;
                                last_approved_node := current_node;
                            END IF;
                        END IF;

                    END LOOP;
                    IF last_approved_node IS NULL THEN
                        INSERT INTO workflow_request_process_path_data_from_store_procedure (
                            status,
                            id,
                            parent_id,
                            type,
                            data
                        ) VALUES (
                            'success',
                            0,
                            previous_node.id,
                            workflow_type::TEXT,
                            '[[]]'::jsonb
                        );
                    ELSE
                        INSERT INTO workflow_request_process_path_data_from_store_procedure (
                            status,
                            id,
                            parent_id,
                            type,
                            data
                        ) VALUES (
                            'success',
                            last_approved_node.id,
                            last_approved_node.workflow_detail_parent_id,
                            workflow_type::TEXT,
                            last_approved_node.workflow_detail_data_object::jsonb
                        );
                    END IF;
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