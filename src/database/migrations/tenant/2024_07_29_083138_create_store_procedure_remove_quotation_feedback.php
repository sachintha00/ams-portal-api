<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_REMOVE_QUOTATION_FEEDBACK(
                p_quotation_feedback_id bigint
            ) LANGUAGE plpgsql
            AS $$
            BEGIN
                IF p_quotation_feedback_id IS NULL OR p_quotation_feedback_id = 0 THEN
                    RAISE EXCEPTION 'Quotation feedback ID cannot be null or zero';
                END IF;

                IF NOT EXISTS (SELECT 1 FROM quotation_feedbacks WHERE id = p_quotation_feedback_id) THEN
                    RAISE EXCEPTION 'Quotation feedback ID % does not exist', p_quotation_feedback_id;
                END IF;

                DELETE FROM quotation_feedbacks WHERE id = p_quotation_feedback_id;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_REMOVE_QUOTATION_FEEDBACK');
    }
};