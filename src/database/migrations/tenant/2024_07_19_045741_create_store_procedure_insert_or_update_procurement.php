<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_INSERT_OR_UPDATE_PROCUREMENT(
                p_request_id VARCHAR(191),
                p_procurement_request_user INT,
                p_date DATE,
                p_selected_items JSONB,
                p_selected_suppliers JSONB,
                p_rpf_document JSONB,
                p_attachment JSONB,
                p_required_date DATE,
                p_comment VARCHAR(191),
                p_procurement_status VARCHAR(191),
                p_id BIGINT DEFAULT NULL
            ) LANGUAGE plpgsql
            AS $$
            DECLARE
                return_id BIGINT;
            BEGIN
                DROP TABLE IF EXISTS procurement_add_response_from_store_procedure;
                CREATE TEMP TABLE procurement_add_response_from_store_procedure(
                    status TEXT,
                    message TEXT,
                    procurement_id BIGINT
                );
                
                IF p_id IS NULL OR p_id = 0 THEN
                    INSERT INTO public.procurements (
                        request_id, procurement_by, date, selected_items, selected_suppliers, rpf_document,
                        attachment, required_date, comment, procurement_status,
                        created_at, updated_at
                    ) VALUES (
                        p_request_id, p_procurement_request_user, p_date, p_selected_items, p_selected_suppliers, p_rpf_document,
                        p_attachment, p_required_date, p_comment, p_procurement_status,
                        NOW(), NOW()
                    ) RETURNING id INTO return_id;

                    INSERT INTO procurement_add_response_from_store_procedure (status, message, procurement_id)
                    VALUES ('SUCCESS', 'Procurement Added successfully', return_id);

                ELSE
                    UPDATE public.procurements
                    SET 
                        procurement_by = p_procurement_request_user,
                        date = p_date,
                        selected_items = p_selected_items,
                        procurement_status = p_procurement_status,
                        updated_at = NOW()
                    WHERE id = p_id RETURNING id INTO return_id;

                    IF FOUND THEN
                        INSERT INTO procurement_add_response_from_store_procedure (status, message, procurement_id)
                        VALUES ('SUCCESS', 'Procurement updated successfully', return_id);
                    ELSE
                        RAISE EXCEPTION 'Procurement with id % not found', p_id;
                    END IF;
                END IF;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_INSERT_OR_UPDATE_PROCUREMENT');
    }
};