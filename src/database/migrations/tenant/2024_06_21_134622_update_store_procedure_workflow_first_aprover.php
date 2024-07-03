<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $procedure = <<<SQL
        CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_GET_WORKFLOW_REQUEST_FIRST_APPROVER(
            IN p_workflow_id INT,
            IN p_value INT
        )
        LANGUAGE plpgsql
        AS $$
        DECLARE
            record RECORD;
            condition JSON;
            condition_sql TEXT;
            combined_condition_sql TEXT := NULL;
            condition_result BOOLEAN;
            combined_condition_result BOOLEAN := FALSE;
            first_detail_processed BOOLEAN := FALSE;
            
            v_designation_id BIGINT;
            designation_name TEXT;
            final_object JSONB;
            
            users_json JSONB;
            user_data jsonb;
            user_details_according_to_designations JSONB;
            user_id INT;
            testuser RECORD;
            updated_users_json jsonb;
            user_object jsonb;
            user_array jsonb[];
        BEGIN
            DROP TABLE IF EXISTS workflow_request_first_approver_data_from_store_procedure;
            FOR record IN
                SELECT wd.id, wd.workflow_detail_parent_id, wd.workflow_detail_type_id, wd.workflow_detail_behavior_type_id, 
                    wd.workflow_detail_order, wd.workflow_detail_level, wd.workflow_detail_data_object, 
                    wd.created_at, wd.updated_at, wt.id AS workflow_type_id, wt.workflow_type
                FROM public.workflow_details wd
                JOIN public.workflow_types wt ON wd.workflow_detail_type_id = wt.id
                WHERE wd.workflow_id = p_workflow_id
                ORDER BY wd.workflow_detail_order
            LOOP
                IF record.workflow_type_id = 1 THEN
                    IF jsonb_typeof(record.workflow_detail_data_object::jsonb) = 'array' THEN
                        condition := record.workflow_detail_data_object->0;
                    ELSE
                        condition := record.workflow_detail_data_object;
                    END IF;

                    IF condition->>'behaviourType' = 'EMPLOYEE' THEN
                        IF (condition->>'condition')::BOOLEAN = combined_condition_result THEN
                            CREATE TEMP TABLE workflow_request_first_approver_data_from_store_procedure (
                                status TEXT,
                                request_node_status TEXT,
                                behaviourType TEXT,
                                type TEXT,
                                data JSONB
                            );

                            users_json := (condition->>'users')::jsonb;

                            FOR user_data IN SELECT * FROM jsonb_array_elements(users_json)
                            LOOP
                                user_id := (user_data->>'id')::INT;
                                
                                SELECT * INTO testuser FROM users WHERE id = user_id;

                                user_object := jsonb_build_object(
                                    'id', testuser.id, 
                                    'name', testuser.name,
                                    'profile_image', testuser.profie_image
                                );

                                user_array := array_append(user_array, user_object);
                            END LOOP;

                            updated_users_json := jsonb_agg(user_array)->0;

                            INSERT INTO workflow_request_first_approver_data_from_store_procedure (status, request_node_status, behaviourType, type, data) 
                            VALUES ('success', 'PENDING', condition->>'behaviourType', condition->>'type', updated_users_json);

                            RETURN;
                        ELSE
                            CONTINUE;
                        END IF;
                    ELSEIF condition->>'behaviourType' = 'DESIGNATION' THEN
                        IF condition->>'type' = 'SINGLE' THEN
                            IF (condition->>'condition')::BOOLEAN = combined_condition_result THEN
                                CREATE TEMP TABLE workflow_request_first_approver_data_from_store_procedure (
                                    status TEXT,
                                    request_node_status TEXT,
                                    behaviourType TEXT,
                                    type TEXT,
                                    data JSONB
                                );
                                v_designation_id := (jsonb_array_elements((condition->>'designation')::jsonb)->>'id')::BIGINT;
                                designation_name := (jsonb_array_elements((condition->>'designation')::jsonb)->>'name')::TEXT;
                                SELECT jsonb_agg(
                                    jsonb_build_object(
                                        'id', u.id,
                                        'name', u.name,
                                        'profile_image', u.profie_image
                                    )
                                ) INTO user_details_according_to_designations
                                FROM public.users u
                                INNER JOIN public.designations d ON u.designation_id = d.id
                                WHERE d.id = v_designation_id;

                                final_object := jsonb_build_object(
                                    'designation', designation_name,
                                    'users', user_details_according_to_designations
                                );

                                INSERT INTO workflow_request_first_approver_data_from_store_procedure (status, request_node_status, behaviourType, type, data) 
                                VALUES ('success', 'PENDING', condition->>'behaviourType', condition->>'type', final_object::jsonb);
                                RETURN;
                            ELSE
                                CONTINUE;
                            END IF;
                        ELSEIF condition->>'type' = 'POOL' THEN
                            IF (condition->>'condition')::BOOLEAN = combined_condition_result THEN
                                CREATE TEMP TABLE workflow_request_first_approver_data_from_store_procedure (
                                    status TEXT,
                                    request_node_status TEXT,
                                    behaviourType TEXT,
                                    type TEXT,
                                    data JSONB
                                );

                                INSERT INTO workflow_request_first_approver_data_from_store_procedure (status, request_node_status, behaviourType, type, data) 
                                VALUES ('success', 'PENDING', condition->>'behaviourType', condition->>'type', (condition->>'designation')::jsonb);

                                RETURN;
                            ELSE
                                CONTINUE;
                            END IF;
                        END IF;
                    END IF;
                ELSIF record.workflow_type_id = 2 THEN
                    
                    combined_condition_sql := NULL;

                    FOR condition IN SELECT * FROM json_array_elements(record.workflow_detail_data_object->0->'conditions')
                    LOOP
                        condition_sql := condition::TEXT;
                        condition_sql := trim(both '"' from condition_sql);
                        
                        condition_sql := replace(condition_sql, 'and', ' AND ');
                        condition_sql := replace(condition_sql, 'or', ' OR ');
                        
                        condition_sql := replace(condition_sql, '''value''', p_value::TEXT);
                        
                        IF combined_condition_sql IS NULL THEN
                            combined_condition_sql := condition_sql;
                        ELSE
                            combined_condition_sql := combined_condition_sql || ' AND ' || condition_sql;
                        END IF;
                    END LOOP;

                    EXECUTE 'SELECT (' || combined_condition_sql || ')::BOOLEAN' INTO combined_condition_result;
                END IF;
                
                EXIT WHEN first_detail_processed;
                first_detail_processed := TRUE;
            END LOOP;
        END;
        $$;
        SQL;    

        // Execute the SQL statement
        DB::unprepared($procedure);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_GET_REQUEST_WORKFLOW');
    }
};