<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_PROCUREMENT_IDS()
            AS $$
            BEGIN
                DROP TABLE IF EXISTS procurement_ids_from_store_procedure;

                CREATE TEMP TABLE procurement_ids_from_store_procedure AS
                SELECT id, request_id FROM
                    procurements
                ORDER BY procurements.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_PROCUREMENT_IDS');
    }
};