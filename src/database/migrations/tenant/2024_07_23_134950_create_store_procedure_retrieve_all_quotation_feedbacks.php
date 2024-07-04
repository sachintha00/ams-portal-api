<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_QUOTATION_FEEDBACK(
                IN p_quotation_feedback_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS quotation_feedbacks_from_store_procedure;

                CREATE TEMP TABLE quotation_feedbacks_from_store_procedure AS
                SELECT * FROM
                    quotation_feedbacks 
                WHERE
                    (quotation_feedbacks.id = p_quotation_feedback_id OR p_quotation_feedback_id IS NULL OR p_quotation_feedback_id = 0)
                ORDER BY quotation_feedbacks.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_QUOTATION_FEEDBACK');
    }
};