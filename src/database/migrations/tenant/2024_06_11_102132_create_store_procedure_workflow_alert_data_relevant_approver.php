<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $procedure = <<<SQL
                CREATE OR REPLACE PROCEDURE public.store_procedure_workflow_alert_data_relevant_approver(
                    IN p_user_id bigint)
                LANGUAGE 'plpgsql'
                AS $$
                DECLARE
                    condition JSON;
                    condition_sql TEXT;
                    combined_condition_sql TEXT := NULL;
                    condition_result BOOLEAN;
                    combined_condition_result BOOLEAN := FALSE;
                    
                    workflow_data RECORD;
                    workflow RECORD;
                    workflow_record RECORD;
                    workflow_data_obj JSONB;
                    workflow_request_type TEXT;
                    workflow_request_type_id INT;
                    pending_workflow_nodeid BIGINT;

                    requested_workflow_id BIGINT;
                    requested_user_id BIGINT;
                    request_value BIGINT;
                    request_data_obj JSONB;
                    requested_user_data JSONB;
                    asset_request_id BIGINT;

                    designation_json JSONB;
                    designation_ids INT[];
                    designation_id_from_data_obj INT[];
                    user_designation_id INT;

                    user_ids INT[];
                    user_details JSONB[];
                    user_details_final JSONB := '[]'::JSONB;
                    user_detail JSONB;
                    user_data JSONB;
                    users_json JSONB;
                    user_object JSONB;
                    selected_user_data JSONB;
                    testuser RECORD;
                    user_array JSONB[];

                    next_approver_user_obj JSONB;
                    next_approver_behaviour_type TEXT;
                    next_approver_type TEXT;

                    approvers_user_id BIGINT;

                    previous_user_id BIGINT;
                    previous_approver_comment TEXT;
                    previous_user JSONB;

                    designation_data JSONB;
                    workflow_node_data_obj JSONB;
                    data_object JSONB;
                    temp_data_object JSONB;

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
                        previous_user_details JSONB,
                        requested_user JSONB,
                        workflow_request_type TEXT,
                        workflow_request_type_id INT,
                        workflow_id BIGINT,
                        requested_id BIGINT,
                        pending_workflow_node_id BIGINT,
                        requested_data_obj JSONB,
                        request_status TEXT,
                        next_approver_behaviour_type TEXT,
                        next_approver_type TEXT,
                        next_approver_details JSONB
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

                            IF workflow_data_obj->>'behaviourType' = 'EMPLOYEE' THEN
                                FOR user_data IN SELECT * FROM jsonb_array_elements((workflow_data_obj->>'users')::jsonb)
                                LOOP
                                    IF (user_data->>'id')::BIGINT = p_user_id THEN
                                        SELECT wrq.id, wrq.user_id, wrq.value, wrq.workflow_id, wrq.requisition_data_object, wrt.request_type, wrt.id
                                            INTO asset_request_id, requested_user_id, request_value, requested_workflow_id, request_data_obj, workflow_request_type, workflow_request_type_id
                                        FROM public.workflow_request_queues wrq
                                        JOIN public.workflow_request_types wrt
                                        ON wrq.workflow_request_type = wrt.id
                                            WHERE wrq.id = workflow_data.request_id
                                        LIMIT 1;

                                        SELECT workflow_node_id
                                            INTO pending_workflow_nodeid
                                        FROM public.workflow_request_queue_details
                                        WHERE request_id = asset_request_id
                                        AND request_status_from_level = 'PENDING';

                                        SELECT jsonb_agg(
                                                jsonb_build_object(
                                                    'user_name', u.user_name,
                                                    'email', u.email,
                                                    'name', u.name,
                                                    'contact_no', u.contact_no,
                                                    'profile_image', u.profie_image,
                                                    'contact_person', u.contact_person,
                                                    'website', u.website,
                                                    'address', u.address,
                                                    'employee_code', u.employee_code,
                                                    'user_description', u.user_description,
                                                    'designation_id', u.designation_id,
                                                    'designation', d.designation,
                                                    'comment_for_action', wrqd.comment_for_action,
                                                        'approved_at', wrqd.updated_at
                                                )
                                            )
                                        INTO previous_user
                                        FROM public.workflow_request_queue_details wrqd
                                        JOIN public.workflow_request_queues wrq ON wrqd.request_id = wrq.id
                                        JOIN public.users u ON wrqd.approver_user_id = u.id
                                        LEFT JOIN public.designations d ON u.designation_id = d.id
                                        WHERE wrqd.request_id = asset_request_id
                                        AND wrqd.request_status_from_level = 'APPROVED';

                                        SELECT json_build_object(
                                        'id', u.id,
                                        'user_name', u.user_name,
                                        'email', u.email,
                                        'name', u.name,
                                        'contact_no', u.contact_no,
                                        'profile_image', u.profie_image,
                                        'contact_person', u.contact_person,
                                        'website', u.website,
                                        'address', u.address,
                                        'employee_code', u.employee_code,
                                        'user_description', u.user_description,
                                        'designation_id', u.designation_id,
                                        'designation', d.designation
                                    ) INTO requested_user_data
                                        FROM 
                                            public.users u
                                        LEFT JOIN 
                                            public.designations d
                                        ON 
                                            u.designation_id = d.id
                                        WHERE 
                                            u.id = requested_user_id;

                                        SELECT json_build_object(
                                        'id', u.id,
                                        'user_name', u.user_name,
                                        'email', u.email,
                                        'name', u.name,
                                        'contact_no', u.contact_no,
                                        'profile_image', u.profie_image,
                                        'contact_person', u.contact_person,
                                        'website', u.website,
                                        'address', u.address,
                                        'employee_code', u.employee_code,
                                        'user_description', u.user_description,
                                        'designation_id', u.designation_id,
                                        'designation', d.designation
                                    ) INTO requested_user_data
                                        FROM 
                                            public.users u
                                        LEFT JOIN 
                                            public.designations d
                                        ON 
                                            u.designation_id = d.id
                                        WHERE 
                                            u.id = requested_user_id;

                                
                                        SELECT id, workflow_detail_data_object, workflow_detail_type_id
                                            INTO workflow
                                        FROM public.workflow_details
                                            WHERE workflow_detail_parent_id = workflow_data.workflow_node_id
                                        LIMIT 1;

                                        IF workflow.workflow_detail_type_id = 2 THEN
                                            combined_condition_sql := NULL;

                                            FOR condition IN SELECT * FROM json_array_elements(workflow.workflow_detail_data_object->0->'conditions')
                                            LOOP
                                                condition_sql := condition::TEXT;
                                                condition_sql := trim(both '"' from condition_sql);
                                                
                                                condition_sql := replace(condition_sql, 'and', ' AND ');
                                                condition_sql := replace(condition_sql, 'or', ' OR ');
                                                
                                                condition_sql := replace(condition_sql, '''value''', request_value::TEXT);
                                                
                                                IF combined_condition_sql IS NULL THEN
                                                    combined_condition_sql := condition_sql;
                                                ELSE
                                                    combined_condition_sql := combined_condition_sql || ' AND ' || condition_sql;
                                                END IF;
                                            END LOOP;
                                
                                            EXECUTE 'SELECT (' || combined_condition_sql || ')::BOOLEAN' INTO combined_condition_result;

                                            FOR workflow_record IN
                                                SELECT id, workflow_detail_parent_id, workflow_id, workflow_detail_type_id, 
                                                    workflow_detail_behavior_type_id, workflow_detail_order, 
                                                    workflow_detail_level, workflow_detail_data_object, created_at, updated_at
                                                FROM public.workflow_details
                                                WHERE workflow_detail_parent_id = workflow.id
                                            LOOP
                                                IF jsonb_typeof(workflow_record.workflow_detail_data_object::jsonb) = 'array' THEN
                                                    data_object := workflow_record.workflow_detail_data_object->0;
                                                ELSE
                                                    data_object := workflow_record.workflow_detail_data_object;
                                                END IF;

                                                IF ((data_object->>'condition')::BOOLEAN = combined_condition_result
                                                    AND workflow.id = workflow_record.workflow_detail_parent_id)
                                                    OR ((data_object->>'isConditionResult')::BOOLEAN = FALSE::BOOLEAN
                                                    AND workflow.id = workflow_record.workflow_detail_parent_id)
                                                THEN

                                                    next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                    next_approver_type := (data_object->>'type')::TEXT;
                                                    users_json := (data_object->>'users')::JSONB;

                                                    SELECT ARRAY(SELECT (jsonb_array_elements(users_json)->>'id')::INT)
                                                    INTO user_ids;

                                                    SELECT ARRAY(
                                                        SELECT jsonb_build_object(
                                                            'id', u.id,
                                                            'user_name', u.user_name,
                                                            'email', u.email,
                                                            'name', u.name,
                                                            'contact_no', u.contact_no,
                                                            'profile_image', u.profie_image,
                                                            'contact_person', u.contact_person,
                                                            'website', u.website,
                                                            'address', u.address,
                                                            'employee_code', u.employee_code,
                                                            'user_description', u.user_description,
                                                            'designation_id', u.designation_id,
                                                            'designation', d.designation
                                                        )
                                                        FROM users u
                                                        LEFT JOIN designations d ON u.designation_id = d.id
                                                        WHERE u.id = ANY(user_ids)
                                                    ) INTO user_details;

                                                    user_details_final := '[]'::JSONB;

                                                    FOR i IN 1..array_length(user_details, 1) LOOP
                                                        user_detail := user_details[i];
                                                        IF NOT EXISTS (
                                                            SELECT 1
                                                            FROM jsonb_array_elements(user_details_final) AS elem
                                                            WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                            AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                            AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                            AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                            AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                            AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                            AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                            AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                            AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                            AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                            AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                            AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                            AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                        ) THEN
                                                            user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                        END IF;
                                                    END LOOP;
                                                    
                                                    next_approver_user_obj := jsonb_agg(user_details_final)->0;
                                                    RAISE INFO '%', pending_workflow_nodeid;
                                                END IF;
                                            END LOOP;
                                        ELSEIF workflow.workflow_detail_type_id = 1 THEN
                                            IF jsonb_typeof(workflow.workflow_detail_data_object::jsonb) = 'array' THEN
                                                data_object := workflow.workflow_detail_data_object->0;
                                            ELSE
                                                data_object := workflow.workflow_detail_data_object;
                                            END IF;

                                            IF data_object->>'behaviourType' = 'DESIGNATION'::TEXT THEN
                                                IF data_object->>'type' = 'SINGLE'::TEXT THEN
                                                    next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                    next_approver_type := (data_object->>'type')::TEXT;
                                                    designation_json := (data_object->>'designation')::JSONB;

                                                    SELECT ARRAY(SELECT (jsonb_array_elements(designation_json)->>'id')::INT)
                                                    INTO designation_ids;
                    
                                                    SELECT ARRAY(
                                                        SELECT jsonb_build_object(
                                                            'id', u.id,
                                                            'user_name', u.user_name,
                                                            'email', u.email,
                                                            'name', u.name,
                                                            'contact_no', u.contact_no,
                                                            'profile_image', u.profie_image,
                                                            'contact_person', u.contact_person,
                                                            'website', u.website,
                                                            'address', u.address,
                                                            'employee_code', u.employee_code,
                                                            'user_description', u.user_description,
                                                            'designation_id', u.designation_id,
                                                            'designation', d.designation
                                                        )
                                                        FROM users u
                                                        LEFT JOIN designations d ON u.designation_id = d.id
                                                        WHERE u.designation_id = ANY(designation_ids)
                                                    ) INTO user_details;
                    
                                                    user_details_final := '[]'::JSONB;
                        
                                                    FOR i IN 1..array_length(user_details, 1) LOOP
                                                        user_detail := user_details[i];
                                                        IF NOT EXISTS (
                                                            SELECT 1
                                                            FROM jsonb_array_elements(user_details_final) AS elem
                                                                WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                                AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                                AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                                AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                                AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                                AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                                AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                                AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                                AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                                AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                                AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                                AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                                AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                        ) THEN
                                                            user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                        END IF;
                                                    END LOOP;
                                                    
                                                    next_approver_user_obj := jsonb_agg(user_details_final)->0;
                    
                                                    RAISE INFO '%', next_approver_user_obj;
                                                    RAISE INFO '%', pending_workflow_nodeid;
                                                ELSEIF data_object->'type' = 'POOL'::TEXT THEN
                                                    next_approver_type := (data_object->>'type')::TEXT;
                                                    RAISE INFO 'POOL 1';
                                                END IF;
                                            ELSEIF data_object->>'behaviourType' = 'EMPLOYEE'::TEXT THEN
                                                next_approver_behaviour_type := workflow_data_obj->>'behaviourType'::TEXT;
                                                next_approver_type := (data_object->>'type')::TEXT;
                                                users_json := (data_object->>'users')::JSONB;

                                                SELECT ARRAY(SELECT (jsonb_array_elements(users_json)->>'id')::INT)
                                                INTO user_ids;
                    
                                                SELECT ARRAY(
                                                        SELECT jsonb_build_object(
                                                            'id', u.id,
                                                            'user_name', u.user_name,
                                                            'email', u.email,
                                                            'name', u.name,
                                                            'contact_no', u.contact_no,
                                                            'profile_image', u.profie_image,
                                                            'contact_person', u.contact_person,
                                                            'website', u.website,
                                                            'address', u.address,
                                                            'employee_code', u.employee_code,
                                                            'user_description', u.user_description,
                                                            'designation_id', u.designation_id,
                                                            'designation', d.designation
                                                        )
                                                        FROM users u
                                                        LEFT JOIN designations d ON u.designation_id = d.id
                                                        WHERE u.id = ANY(user_ids)
                                                    ) INTO user_details;
                    
                                                user_details_final := '[]'::JSONB;
                    
                                                FOR i IN 1..array_length(user_details, 1) LOOP
                                                    user_detail := user_details[i];
                                                    IF NOT EXISTS (
                                                        SELECT 1
                                                        FROM jsonb_array_elements(user_details_final) AS elem
                                                        WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                        AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                        AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                        AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                        AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                        AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                        AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                        AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                        AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                        AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                        AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                        AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                        AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                    ) THEN
                                                        user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                    END IF;
                                                END LOOP;
                                                
                                                next_approver_user_obj := jsonb_agg(user_details_final)->0;
                                                RAISE INFO '%', next_approver_user_obj;
                                            END IF;
                                        END IF;

                                        INSERT INTO workflow_alert_data_from_store_procedure (
                                            requested_user,
                                            workflow_request_type,
                                            workflow_request_type_id,
                                            workflow_id,
                                            requested_id,
                                            pending_workflow_node_id,
                                            previous_user_details,
                                            requested_data_obj,
                                            request_status,
                                            next_approver_behaviour_type,
                                            next_approver_type,
                                            next_approver_details
                                        ) VALUES (
                                            requested_user_data::JSONB,
                                            workflow_request_type::TEXT,
                                            workflow_request_type_id::INT,
                                            requested_workflow_id::BIGINT,
                                            asset_request_id::BIGINT,
                                            pending_workflow_nodeid::BIGINT,
                                            previous_user,
                                            request_data_obj::JSONB,
                                            'PENDING',
                                            next_approver_behaviour_type::TEXT,
                                            next_approver_type::TEXT,
                                            next_approver_user_obj::JSONB
                                        );
                                    END IF;
                                END LOOP;
                            END IF;
                            IF workflow_data_obj->>'behaviourType' = 'DESIGNATION' THEN
                                IF workflow_data_obj->>'type' = 'POOL' THEN
                                    FOR designation_data IN SELECT * FROM jsonb_array_elements((workflow_data_obj->>'designation')::jsonb)
                                    LOOP
                                        FOR approvers_user_id IN 
                                            SELECT id 
                                            FROM public.users 
                                            WHERE designation_id = (designation_data->>'id')::BIGINT AND id = p_user_id
                                        LOOP
                                            IF approvers_user_id = p_user_id THEN
                                                SELECT wrq.id, wrq.user_id, wrq.value, wrq.workflow_id, wrq.requisition_data_object, wrt.request_type, wrt.id
                                                    INTO asset_request_id, requested_user_id, request_value, requested_workflow_id, request_data_obj, workflow_request_type, workflow_request_type_id
                                                FROM public.workflow_request_queues wrq
                                                JOIN public.workflow_request_types wrt
                                                ON wrq.workflow_request_type = wrt.id
                                                    WHERE wrq.id = workflow_data.request_id
                                                LIMIT 1;

                                                SELECT workflow_node_id
                                                    INTO pending_workflow_nodeid
                                                FROM public.workflow_request_queue_details
                                                WHERE request_id = asset_request_id
                                                AND request_status_from_level = 'PENDING';

                                                SELECT jsonb_agg(
                                                        jsonb_build_object(
                                                            'user_name', u.user_name,
                                                            'email', u.email,
                                                            'name', u.name,
                                                            'contact_no', u.contact_no,
                                                            'profile_image', u.profie_image,
                                                            'contact_person', u.contact_person,
                                                            'website', u.website,
                                                            'address', u.address,
                                                            'employee_code', u.employee_code,
                                                            'user_description', u.user_description,
                                                            'designation_id', u.designation_id,
                                                            'designation', d.designation,
                                                            'comment_for_action', wrqd.comment_for_action,
                                                                'approved_at', wrqd.updated_at
                                                        )
                                                    )
                                                INTO previous_user
                                                FROM public.workflow_request_queue_details wrqd
                                                JOIN public.workflow_request_queues wrq ON wrqd.request_id = wrq.id
                                                JOIN public.users u ON wrqd.approver_user_id = u.id
                                                LEFT JOIN public.designations d ON u.designation_id = d.id
                                                WHERE wrqd.request_id = asset_request_id
                                                AND wrqd.request_status_from_level = 'APPROVED';

                                                SELECT json_build_object(
                                                        'id', u.id,
                                                        'user_name', u.user_name,
                                                        'email', u.email,
                                                        'name', u.name,
                                                        'contact_no', u.contact_no,
                                                        'profile_image', u.profie_image,
                                                        'contact_person', u.contact_person,
                                                        'website', u.website,
                                                        'address', u.address,
                                                        'employee_code', u.employee_code,
                                                        'user_description', u.user_description,
                                                        'designation_id', u.designation_id,
                                                        'designation', d.designation
                                                    ) INTO requested_user_data
                                                FROM 
                                                    public.users u
                                                LEFT JOIN 
                                                    public.designations d
                                                ON 
                                                    u.designation_id = d.id
                                                WHERE 
                                                    u.id = requested_user_id;

                                                SELECT id, workflow_detail_data_object, workflow_detail_type_id
                                                    INTO workflow
                                                FROM public.workflow_details
                                                    WHERE workflow_detail_parent_id = workflow_data.workflow_node_id
                                                LIMIT 1;

                                                IF workflow.workflow_detail_type_id = 2 THEN
                                                    combined_condition_sql := NULL;
                        
                                                    FOR condition IN SELECT * FROM json_array_elements(workflow.workflow_detail_data_object->0->'conditions')
                                                    LOOP
                                                        condition_sql := condition::TEXT;
                                                        condition_sql := trim(both '"' from condition_sql);
                                                        
                                                        condition_sql := replace(condition_sql, 'and', ' AND ');
                                                        condition_sql := replace(condition_sql, 'or', ' OR ');
                                                        
                                                        condition_sql := replace(condition_sql, '''value''', request_value::TEXT);
                                                        
                                                        IF combined_condition_sql IS NULL THEN
                                                            combined_condition_sql := condition_sql;
                                                        ELSE
                                                            combined_condition_sql := combined_condition_sql || ' AND ' || condition_sql;
                                                        END IF;
                                                    END LOOP;
                                        
                                                    EXECUTE 'SELECT (' || combined_condition_sql || ')::BOOLEAN' INTO combined_condition_result;
                        
                        
                                                    FOR workflow_record IN
                                                        SELECT id, workflow_detail_parent_id, workflow_id, workflow_detail_type_id, 
                                                            workflow_detail_behavior_type_id, workflow_detail_order, 
                                                            workflow_detail_level, workflow_detail_data_object, created_at, updated_at
                                                        FROM public.workflow_details
                                                        WHERE workflow_detail_parent_id = workflow.id
                                                    LOOP
                                                        IF jsonb_typeof(workflow_record.workflow_detail_data_object::jsonb) = 'array' THEN
                                                            data_object := workflow_record.workflow_detail_data_object->0;
                                                        ELSE
                                                            data_object := workflow_record.workflow_detail_data_object;
                                                        END IF;
                        
                                                        IF ((data_object->>'condition')::BOOLEAN = combined_condition_result
                                                            AND workflow.id = workflow_record.workflow_detail_parent_id)
                                                            OR ((data_object->>'isConditionResult')::BOOLEAN = FALSE::BOOLEAN
                                                            AND workflow.id = workflow_record.workflow_detail_parent_id)
                                                        THEN
                                                            next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                            next_approver_type := (data_object->>'type')::TEXT;
                                                            users_json := (data_object->>'users')::JSONB;

                                                            -- RAISE INFO '%',data_object;

                                                            SELECT ARRAY(SELECT (jsonb_array_elements(users_json)->>'id')::INT)
                                                            INTO user_ids;
                        
                                                            SELECT ARRAY(
                                                                SELECT jsonb_build_object(
                                                                    'id', u.id,
                                                                    'user_name', u.user_name,
                                                                    'email', u.email,
                                                                    'name', u.name,
                                                                    'contact_no', u.contact_no,
                                                                    'profile_image', u.profie_image,
                                                                    'contact_person', u.contact_person,
                                                                    'website', u.website,
                                                                    'address', u.address,
                                                                    'employee_code', u.employee_code,
                                                                    'user_description', u.user_description,
                                                                    'designation_id', u.designation_id,
                                                                    'designation', d.designation
                                                                )
                                                                FROM users u
                                                                LEFT JOIN designations d ON u.designation_id = d.id
                                                                WHERE u.id = ANY(user_ids)
                                                            ) INTO user_details;
                        
                                                            user_details_final := '[]'::JSONB;
                        
                                                            FOR i IN 1..array_length(user_details, 1) LOOP
                                                                user_detail := user_details[i];
                                                                IF NOT EXISTS (
                                                                    SELECT 1
                                                                    FROM jsonb_array_elements(user_details_final) AS elem
                                                                        WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                                        AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                                        AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                                        AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                                        AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                                        AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                                        AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                                        AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                                        AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                                        AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                                        AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                                        AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                                        AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                                ) THEN
                                                                    user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                                END IF;
                                                            END LOOP;
                                                            
                                                            next_approver_user_obj := jsonb_agg(user_details_final)->0;
                                                            -- RAISE INFO '%', next_approver_user_obj;
                                                        END IF;
                                                    END LOOP;
                                                ELSEIF workflow.workflow_detail_type_id = 1 THEN
                                                    IF jsonb_typeof(workflow.workflow_detail_data_object::jsonb) = 'array' THEN
                                                        data_object := workflow.workflow_detail_data_object->0;
                                                    ELSE
                                                        data_object := workflow.workflow_detail_data_object;
                                                    END IF;
                        
                                                    IF data_object->>'behaviourType' = 'DESIGNATION'::TEXT THEN
                                                        IF data_object->>'type' = 'SINGLE'::TEXT THEN
                                                            next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                            next_approver_type := (data_object->>'type')::TEXT;
                                                            designation_json := (data_object->>'designation')::JSONB;
                        
                                                            SELECT ARRAY(SELECT (jsonb_array_elements(designation_json)->>'id')::INT)
                                                            INTO designation_ids;
                            
                                                            SELECT ARRAY(
                                                                SELECT jsonb_build_object(
                                                                    'id', u.id,
                                                                    'user_name', u.user_name,
                                                                    'email', u.email,
                                                                    'name', u.name,
                                                                    'contact_no', u.contact_no,
                                                                    'profile_image', u.profie_image,
                                                                    'contact_person', u.contact_person,
                                                                    'website', u.website,
                                                                    'address', u.address,
                                                                    'employee_code', u.employee_code,
                                                                    'user_description', u.user_description,
                                                                    'designation_id', u.designation_id,
                                                                    'designation', d.designation
                                                                )
                                                                FROM users u
                                                                LEFT JOIN designations d ON u.designation_id = d.id
                                                                WHERE u.designation_id = ANY(designation_ids)
                                                            ) INTO user_details;
                            
                                                            user_details_final := '[]'::JSONB;
                                
                                                            FOR i IN 1..array_length(user_details, 1) LOOP
                                                                user_detail := user_details[i];
                                                                IF NOT EXISTS (
                                                                    SELECT 1
                                                                    FROM jsonb_array_elements(user_details_final) AS elem
                                                                        WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                                        AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                                        AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                                        AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                                        AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                                        AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                                        AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                                        AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                                        AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                                        AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                                        AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                                        AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                                        AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                                ) THEN
                                                                    user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                                END IF;
                                                            END LOOP;
                                                            
                                                            next_approver_user_obj := jsonb_agg(user_details_final)->0;
                            
                                                            RAISE INFO '%', next_approver_user_obj;
                                                        ELSEIF data_object->'type' = 'POOL'::TEXT THEN
                                                            next_approver_type := (data_object->>'type')::TEXT;
                                                            RAISE INFO 'POOL 2';
                                                        END IF;
                                                    ELSEIF data_object->>'behaviourType' = 'EMPLOYEE'::TEXT THEN
                                                        next_approver_behaviour_type := workflow_data_obj->>'behaviourType'::TEXT;
                                                        next_approver_type := (data_object->>'type')::TEXT;
                                                        users_json := (data_object->>'users')::JSONB;
                        
                                                        SELECT ARRAY(SELECT (jsonb_array_elements(users_json)->>'id')::INT)
                                                        INTO user_ids;
                            
                                                        SELECT ARRAY(
                                                        SELECT jsonb_build_object(
                                                            'id', u.id,
                                                            'user_name', u.user_name,
                                                            'email', u.email,
                                                            'name', u.name,
                                                            'contact_no', u.contact_no,
                                                            'profile_image', u.profie_image,
                                                            'contact_person', u.contact_person,
                                                            'website', u.website,
                                                            'address', u.address,
                                                            'employee_code', u.employee_code,
                                                            'user_description', u.user_description,
                                                            'designation_id', u.designation_id,
                                                            'designation', d.designation
                                                        )
                                                        FROM users u
                                                        LEFT JOIN designations d ON u.designation_id = d.id
                                                        WHERE u.id = ANY(user_ids)
                                                    ) INTO user_details;
                            
                                                        user_details_final := '[]'::JSONB;
                            
                                                        FOR i IN 1..array_length(user_details, 1) LOOP
                                                            user_detail := user_details[i];
                                                            IF NOT EXISTS (
                                                                SELECT 1
                                                                FROM jsonb_array_elements(user_details_final) AS elem
                                                                WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                                AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                                AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                                AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                                AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                                AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                                AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                                AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                                AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                                AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                                AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                                AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                                AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                            ) THEN
                                                                user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                            END IF;
                                                        END LOOP;
                                                        
                                                        next_approver_user_obj := jsonb_agg(user_details_final)->0;
                                                        RAISE INFO '%', next_approver_user_obj;
                                                    END IF;
                                                END IF;

                                                INSERT INTO workflow_alert_data_from_store_procedure (
                                                    requested_user,
                                                    workflow_request_type,
                                                    workflow_request_type_id,
                                                    workflow_id,
                                                    requested_id,
                                                    pending_workflow_node_id,
                                                    previous_user_details,
                                                    requested_data_obj,
                                                    request_status,
                                                    next_approver_behaviour_type,
                                                    next_approver_type,
                                                    next_approver_details
                                                ) VALUES (
                                                    requested_user_data::JSONB,
                                                    workflow_request_type::TEXT,
                                                    workflow_request_type_id::INT,
                                                    requested_workflow_id::BIGINT,
                                                    asset_request_id::BIGINT,
                                                    pending_workflow_nodeid::BIGINT,
                                                    previous_user,
                                                    request_data_obj::JSONB,
                                                    'PENDING',
                                                    next_approver_behaviour_type::TEXT,
                                                    next_approver_type::TEXT,
                                                    next_approver_user_obj::JSONB
                                                );

                                                -- RAISE INFO 'Workflow id %', requested_workflow_id;
                                                -- RAISE INFO 'Requested User %', requested_user_data;
                                                -- RAISE INFO 'Workflow Request Type %', workflow_request_type;
                                                -- RAISE INFO 'Request Data Obj %', request_data_obj;
                                                -- RAISE INFO 'Next approver %', next_approver_user_obj;
                                            END IF;
                                        END LOOP;
                                    END LOOP;
                                END IF;
                                IF workflow_data_obj->>'type' = 'SINGLE' THEN
                                    IF jsonb_typeof(workflow_data.workflow_detail_data_object::jsonb) = 'array' THEN
                                        temp_data_object := workflow_data.workflow_detail_data_object->0;
                                    ELSE
                                        temp_data_object := workflow_data.workflow_detail_data_object;
                                    END IF;

                                    SELECT ARRAY(SELECT (jsonb_array_elements((temp_data_object->>'designation')::JSONB)->>'id')::INT)
                                    INTO designation_id_from_data_obj;

                                    SELECT designation_id
                                        INTO user_designation_id
                                    FROM public.users
                                    WHERE id = p_user_id;

                                    RAISE INFO 'User designation ID: %', user_designation_id;

                                    IF user_designation_id = ANY(designation_id_from_data_obj) THEN
                                        SELECT wrq.id, wrq.user_id, wrq.value, wrq.workflow_id, wrq.requisition_data_object, wrt.request_type, wrt.id
                                            INTO asset_request_id, requested_user_id, request_value, requested_workflow_id, request_data_obj, workflow_request_type, workflow_request_type_id
                                        FROM public.workflow_request_queues wrq
                                        JOIN public.workflow_request_types wrt
                                        ON wrq.workflow_request_type = wrt.id
                                            WHERE wrq.id = workflow_data.request_id
                                        LIMIT 1;

                                        SELECT workflow_node_id
                                            INTO pending_workflow_nodeid
                                        FROM public.workflow_request_queue_details
                                        WHERE request_id = asset_request_id
                                        AND request_status_from_level = 'PENDING';

                                        SELECT jsonb_agg(
                                                jsonb_build_object(
                                                    'user_name', u.user_name,
                                                    'email', u.email,
                                                    'name', u.name,
                                                    'contact_no', u.contact_no,
                                                    'profile_image', u.profie_image,
                                                    'contact_person', u.contact_person,
                                                    'website', u.website,
                                                    'address', u.address,
                                                    'employee_code', u.employee_code,
                                                    'user_description', u.user_description,
                                                    'designation_id', u.designation_id,
                                                    'designation', d.designation,
                                                    'comment_for_action', wrqd.comment_for_action,
                                                        'approved_at', wrqd.updated_at
                                                )
                                            )
                                        INTO previous_user
                                        FROM public.workflow_request_queue_details wrqd
                                        JOIN public.workflow_request_queues wrq ON wrqd.request_id = wrq.id
                                        JOIN public.users u ON wrqd.approver_user_id = u.id
                                        LEFT JOIN public.designations d ON u.designation_id = d.id
                                        WHERE wrqd.request_id = asset_request_id
                                        AND wrqd.request_status_from_level = 'APPROVED';

                                        SELECT json_build_object(
                                        'id', u.id,
                                        'user_name', u.user_name,
                                        'email', u.email,
                                        'name', u.name,
                                        'contact_no', u.contact_no,
                                        'profile_image', u.profie_image,
                                        'contact_person', u.contact_person,
                                        'website', u.website,
                                        'address', u.address,
                                        'employee_code', u.employee_code,
                                        'user_description', u.user_description,
                                        'designation_id', u.designation_id,
                                        'designation', d.designation
                                    ) INTO requested_user_data
                                        FROM 
                                            public.users u
                                        LEFT JOIN 
                                            public.designations d
                                        ON 
                                            u.designation_id = d.id
                                        WHERE 
                                            u.id = requested_user_id;

                                        SELECT id, workflow_detail_data_object, workflow_detail_type_id
                                            INTO workflow
                                        FROM public.workflow_details
                                            WHERE workflow_detail_parent_id = workflow_data.workflow_node_id
                                        LIMIT 1;

                                        IF workflow.workflow_detail_type_id = 2 THEN
                                            combined_condition_sql := NULL;

                                            FOR condition IN SELECT * FROM json_array_elements(workflow.workflow_detail_data_object->0->'conditions')
                                            LOOP
                                                condition_sql := condition::TEXT;
                                                condition_sql := trim(both '"' from condition_sql);
                                                
                                                condition_sql := replace(condition_sql, 'and', ' AND ');
                                                condition_sql := replace(condition_sql, 'or', ' OR ');
                                                
                                                condition_sql := replace(condition_sql, '''value''', request_value::TEXT);
                                                
                                                IF combined_condition_sql IS NULL THEN
                                                    combined_condition_sql := condition_sql;
                                                ELSE
                                                    combined_condition_sql := combined_condition_sql || ' AND ' || condition_sql;
                                                END IF;
                                            END LOOP;
                                
                                            EXECUTE 'SELECT (' || combined_condition_sql || ')::BOOLEAN' INTO combined_condition_result;

                                            FOR workflow_record IN
                                                SELECT id, workflow_detail_parent_id, workflow_id, workflow_detail_type_id, 
                                                    workflow_detail_behavior_type_id, workflow_detail_order, 
                                                    workflow_detail_level, workflow_detail_data_object, created_at, updated_at
                                                FROM public.workflow_details
                                                WHERE workflow_detail_parent_id = workflow.id
                                            LOOP
                                                IF jsonb_typeof(workflow_record.workflow_detail_data_object::jsonb) = 'array' THEN
                                                    data_object := workflow_record.workflow_detail_data_object->0;
                                                ELSE
                                                    data_object := workflow_record.workflow_detail_data_object;
                                                END IF;

                                                IF data_object->>'behaviourType' = 'DESIGNATION'::TEXT THEN
                                                    IF data_object->>'type' = 'SINGLE'::TEXT THEN
                                                        next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                        next_approver_type := (data_object->>'type')::TEXT;
                                                        designation_json := (data_object->>'designation')::JSONB;

                                                        SELECT ARRAY(SELECT (jsonb_array_elements(designation_json)->>'id')::INT)
                                                        INTO designation_ids;
                        
                                                        SELECT ARRAY(
                                                            SELECT jsonb_build_object(
                                                                'id', u.id,
                                                                'user_name', u.user_name,
                                                                'email', u.email,
                                                                'name', u.name,
                                                                'contact_no', u.contact_no,
                                                                'profile_image', u.profie_image,
                                                                'contact_person', u.contact_person,
                                                                'website', u.website,
                                                                'address', u.address,
                                                                'employee_code', u.employee_code,
                                                                'user_description', u.user_description,
                                                                'designation_id', u.designation_id,
                                                                'designation', d.designation
                                                            )
                                                            FROM users u
                                                            LEFT JOIN designations d ON u.designation_id = d.id
                                                            WHERE u.designation_id = ANY(designation_ids)
                                                        ) INTO user_details;
                        
                                                        user_details_final := '[]'::JSONB;
                            
                                                        FOR i IN 1..array_length(user_details, 1) LOOP
                                                            user_detail := user_details[i];
                                                            IF NOT EXISTS (
                                                                SELECT 1
                                                                FROM jsonb_array_elements(user_details_final) AS elem
                                                                    WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                                    AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                                    AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                                    AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                                    AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                                    AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                                    AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                                    AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                                    AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                                    AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                                    AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                                    AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                                    AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                            ) THEN
                                                                user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                            END IF;
                                                        END LOOP;
                                                        
                                                        next_approver_user_obj := jsonb_agg(user_details_final)->0;
                        
                                                        RAISE INFO '%', next_approver_user_obj;
                                                    ELSEIF data_object->'type' = 'POOL'::TEXT THEN
                                                        RAISE INFO 'POOL 3';
                                                    END IF;
                                                ELSEIF data_object->>'behaviourType' = 'EMPLOYEE'::TEXT THEN
                                                    next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                    next_approver_type := (data_object->>'type')::TEXT;
                                                    users_json := (data_object->>'users')::JSONB;

                                                    SELECT ARRAY(SELECT (jsonb_array_elements(users_json)->>'id')::INT)
                                                    INTO user_ids;
                        
                                                    SELECT ARRAY(
                                                            SELECT jsonb_build_object(
                                                                'id', u.id,
                                                                'user_name', u.user_name,
                                                                'email', u.email,
                                                                'name', u.name,
                                                                'contact_no', u.contact_no,
                                                                'profile_image', u.profie_image,
                                                                'contact_person', u.contact_person,
                                                                'website', u.website,
                                                                'address', u.address,
                                                                'employee_code', u.employee_code,
                                                                'user_description', u.user_description,
                                                                'designation_id', u.designation_id,
                                                                'designation', d.designation
                                                            )
                                                            FROM users u
                                                            LEFT JOIN designations d ON u.designation_id = d.id
                                                            WHERE u.id = ANY(user_ids)
                                                        ) INTO user_details;
                        
                                                    user_details_final := '[]'::JSONB;
                        
                                                    FOR i IN 1..array_length(user_details, 1) LOOP
                                                        user_detail := user_details[i];
                                                        IF NOT EXISTS (
                                                            SELECT 1
                                                            FROM jsonb_array_elements(user_details_final) AS elem
                                                            WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                            AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                            AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                            AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                            AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                            AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                            AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                            AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                            AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                            AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                            AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                            AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                            AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                        ) THEN
                                                            user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                        END IF;
                                                    END LOOP;
                                                    
                                                    next_approver_user_obj := jsonb_agg(user_details_final)->0;
                                                    RAISE INFO '%', next_approver_user_obj;
                                                END IF;
                                            END LOOP;
                                        ELSEIF workflow.workflow_detail_type_id = 1 THEN
                                            IF jsonb_typeof(workflow.workflow_detail_data_object::jsonb) = 'array' THEN
                                                data_object := workflow.workflow_detail_data_object->0;
                                            ELSE
                                                data_object := workflow.workflow_detail_data_object;
                                            END IF;

                                            IF data_object->>'behaviourType' = 'DESIGNATION'::TEXT THEN
                                                IF data_object->>'type' = 'SINGLE'::TEXT THEN
                                                    next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                    next_approver_type := (data_object->>'type')::TEXT;
                                                    designation_json := (data_object->>'designation')::JSONB;

                                                    SELECT ARRAY(SELECT (jsonb_array_elements(designation_json)->>'id')::INT)
                                                    INTO designation_ids;
                    
                                                    SELECT ARRAY(
                                                        SELECT jsonb_build_object(
                                                            'id', u.id,
                                                            'user_name', u.user_name,
                                                            'email', u.email,
                                                            'name', u.name,
                                                            'contact_no', u.contact_no,
                                                            'profile_image', u.profie_image,
                                                            'contact_person', u.contact_person,
                                                            'website', u.website,
                                                            'address', u.address,
                                                            'employee_code', u.employee_code,
                                                            'user_description', u.user_description,
                                                            'designation_id', u.designation_id,
                                                            'designation', d.designation
                                                        )
                                                        FROM users u
                                                        LEFT JOIN designations d ON u.designation_id = d.id
                                                        WHERE u.designation_id = ANY(designation_ids)
                                                    ) INTO user_details;
                    
                                                    user_details_final := '[]'::JSONB;
                        
                                                    FOR i IN 1..array_length(user_details, 1) LOOP
                                                        user_detail := user_details[i];
                                                        IF NOT EXISTS (
                                                            SELECT 1
                                                            FROM jsonb_array_elements(user_details_final) AS elem
                                                                WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                                AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                                AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                                AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                                AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                                AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                                AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                                AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                                AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                                AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                                AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                                AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                                AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                        ) THEN
                                                            user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                        END IF;
                                                    END LOOP;
                                                    
                                                    next_approver_user_obj := jsonb_agg(user_details_final)->0;
                    
                                                    RAISE INFO '%', next_approver_user_obj;
                                                ELSEIF data_object->'type' = 'POOL'::TEXT THEN
                                                    RAISE INFO 'POOL 3';
                                                END IF;
                                            ELSEIF data_object->>'behaviourType' = 'EMPLOYEE'::TEXT THEN
                                                next_approver_behaviour_type := data_object->>'behaviourType'::TEXT;
                                                next_approver_type := (data_object->>'type')::TEXT;
                                                users_json := (data_object->>'users')::JSONB;

                                                SELECT ARRAY(SELECT (jsonb_array_elements(users_json)->>'id')::INT)
                                                INTO user_ids;
                    
                                                SELECT ARRAY(
                                                        SELECT jsonb_build_object(
                                                            'id', u.id,
                                                            'user_name', u.user_name,
                                                            'email', u.email,
                                                            'name', u.name,
                                                            'contact_no', u.contact_no,
                                                            'profile_image', u.profie_image,
                                                            'contact_person', u.contact_person,
                                                            'website', u.website,
                                                            'address', u.address,
                                                            'employee_code', u.employee_code,
                                                            'user_description', u.user_description,
                                                            'designation_id', u.designation_id,
                                                            'designation', d.designation
                                                        )
                                                        FROM users u
                                                        LEFT JOIN designations d ON u.designation_id = d.id
                                                        WHERE u.id = ANY(user_ids)
                                                    ) INTO user_details;
                    
                                                user_details_final := '[]'::JSONB;
                    
                                                FOR i IN 1..array_length(user_details, 1) LOOP
                                                    user_detail := user_details[i];
                                                    IF NOT EXISTS (
                                                        SELECT 1
                                                        FROM jsonb_array_elements(user_details_final) AS elem
                                                        WHERE (elem ->> 'id')::TEXT = (user_detail ->> 'id')::TEXT
                                                        AND (elem ->> 'user_name')::TEXT = (user_detail ->> 'user_name')::TEXT
                                                        AND (elem ->> 'email')::TEXT = (user_detail ->> 'email')::TEXT
                                                        AND (elem ->> 'name')::TEXT = (user_detail ->> 'name')::TEXT
                                                        AND (elem ->> 'contact_no')::TEXT = (user_detail ->> 'contact_no')::TEXT
                                                        AND (elem ->> 'profile_image')::TEXT = (user_detail ->> 'profile_image')::TEXT
                                                        AND (elem ->> 'contact_person')::TEXT = (user_detail ->> 'contact_person')::TEXT
                                                        AND (elem ->> 'website')::TEXT = (user_detail ->> 'website')::TEXT
                                                        AND (elem ->> 'address')::TEXT = (user_detail ->> 'address')::TEXT
                                                        AND (elem ->> 'employee_code')::TEXT = (user_detail ->> 'employee_code')::TEXT
                                                        AND (elem ->> 'user_description')::TEXT = (user_detail ->> 'user_description')::TEXT
                                                        AND (elem ->> 'designation_id')::TEXT = (user_detail ->> 'designation_id')::TEXT
                                                        AND (elem ->> 'designation')::TEXT = (user_detail ->> 'designation')::TEXT
                                                    ) THEN
                                                        user_details_final := user_details_final || jsonb_build_array(user_detail);
                                                    END IF;
                                                END LOOP;
                                                
                                                next_approver_user_obj := jsonb_agg(user_details_final)->0;
                                                RAISE INFO '%', next_approver_user_obj;
                                            END IF;
                                        END IF;

                                        INSERT INTO workflow_alert_data_from_store_procedure (
                                            requested_user,
                                            workflow_request_type,
                                            workflow_request_type_id,
                                            workflow_id,
                                            requested_id,
                                            pending_workflow_node_id,
                                            previous_user_details,
                                            requested_data_obj,
                                            request_status,
                                            next_approver_behaviour_type,
                                            next_approver_type,
                                            next_approver_details
                                        ) VALUES (
                                            requested_user_data::JSONB,
                                            workflow_request_type::TEXT,
                                            workflow_request_type_id::INT,
                                            requested_workflow_id::BIGINT,
                                            asset_request_id::BIGINT,
                                            pending_workflow_nodeid::BIGINT,
                                            previous_user,
                                            request_data_obj::JSONB,
                                            'PENDING',
                                            next_approver_behaviour_type::TEXT,
                                            next_approver_type::TEXT,
                                            next_approver_user_obj::JSONB
                                        );
                                    END IF;
                                END IF;
                            END IF;
                        END IF;

                    END LOOP;
                    CLOSE workflows_data_cursor;
                END;
                \$\$;
                SQL;
                
            // Execute the SQL statement
            DB::unprepared($procedure);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_WORKFLOW_ALERT_DATA_RELEVANT_APPROVER');
    }
};