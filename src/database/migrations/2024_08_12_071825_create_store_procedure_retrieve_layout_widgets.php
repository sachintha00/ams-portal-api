<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_DASHBOARD_LAYOUT_WIDGETS(
                IN p_layout_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS dashboard_layout_from_store_procedure;

                CREATE TEMP TABLE dashboard_layout_from_store_procedure AS
                SELECT * FROM
                    layout_widgets 
                WHERE
                    layout_widgets.id = p_layout_id OR p_layout_id IS NULL OR p_layout_id = 0
                ORDER BY layout_widgets.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_DASHBOARD_LAYOUT_WIDGETS');
    }
};