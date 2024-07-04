<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_INSERT_OR_UPDATE_QUOTATION_FEEDBACK(
                p_date DATE,
                p_procurement_id INT,
                p_selected_supplier_id INT,
                p_selected_items JSONB,
                p_required_date DATE,
                p_feedback_fill_by INT,
                p_id BIGINT DEFAULT NULL
            ) LANGUAGE plpgsql
            AS $$
            DECLARE
                return_id BIGINT;
            BEGIN
                DROP TABLE IF EXISTS quotation_feedback_response_from_store_procedure;
                CREATE TEMP TABLE quotation_feedback_response_from_store_procedure(
                    status TEXT,
                    message TEXT,
                    procurement_id BIGINT
                );
                
                IF p_id IS NULL OR p_id = 0 THEN
                    INSERT INTO public.quotation_feedbacks (
                        date, procurement_id, selected_supplier_id, selected_items, available_date, feedback_fill_by,
                        created_at, updated_at
                    ) VALUES (
                        p_date, p_procurement_id, p_selected_supplier_id, p_selected_items, p_required_date, p_feedback_fill_by,
                        NOW(), NOW()
                    ) RETURNING id INTO return_id;

                    INSERT INTO quotation_feedback_response_from_store_procedure (status, message, procurement_id)
                    VALUES ('SUCCESS', 'Quotation feedback added successfully', return_id);

                ELSE
                    UPDATE public.quotation_feedbacks
                    SET 
                        date = p_date,
                        procurement_id = p_procurement_id,
                        selected_supplier_id = p_selected_supplier_id,
                        selected_items = p_selected_items,
                        available_date = p_required_date, 
                        feedback_fill_by = p_feedback_fill_by,
                        updated_at = NOW()
                    WHERE id = p_id RETURNING id INTO return_id;

                    IF FOUND THEN
                        INSERT INTO quotation_feedback_response_from_store_procedure (status, message, procurement_id)
                        VALUES ('SUCCESS', 'Quotation feedback updated successfully', return_id);
                    ELSE
                        RAISE EXCEPTION 'Quotation feedback with id % not found', p_id;
                    END IF;
                END IF;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_INSERT_OR_UPDATE_QUOTATION_FEEDBACK');
    }
};