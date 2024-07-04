<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_PROCUREMENTS_BY_USERID(
                IN p_procurement_by INT,
                IN p_procurement_id INT DEFAULT 0,
                IN p_request_id TEXT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS procurements_by_userid_from_store_procedure;

                CREATE TEMP TABLE procurements_by_userid_from_store_procedure AS
                SELECT p.id, p.request_id, p.procurement_by, p.date, p.selected_items, p.selected_suppliers, 
                    p.rpf_document, p.attachment, p.required_date, p.comment, p.procurement_status, 
                    p.created_at, p.updated_at,
                    COALESCE(jsonb_agg(jsonb_build_object(
                        'id', qf.id,
                        'date', qf.date,
                        'procurement_id', qf.procurement_id,
                        'selected_supplier_id', qf.selected_supplier_id,
                        'selected_supplier_name', (SELECT name from supplair WHERE id = qf.selected_supplier_id),
                        'selected_items', qf.selected_items,
                        'available_date', qf.available_date,
                        'feedback_fill_by', qf.feedback_fill_by,
                        'created_at', qf.created_at,
                        'updated_at', qf.updated_at
                    )) FILTER (WHERE qf.id IS NOT NULL), '[]'::jsonb) AS quotation_feedbacks
                FROM procurements p
                LEFT JOIN quotation_feedbacks qf ON p.id = qf.procurement_id
                WHERE (p_procurement_id != 0 AND p.id = p_procurement_id)
                OR (p_procurement_id = 0 AND (p.request_id = p_request_id OR p_request_id IS NULL OR p_request_id = NULL))
                AND p.procurement_by = p_procurement_by
                GROUP BY p.id
                ORDER BY p.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_PROCUREMENTS_BY_USERID');
    }
};