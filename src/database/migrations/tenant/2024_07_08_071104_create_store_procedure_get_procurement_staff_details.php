<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_GET_PROCUREMENT_STAFF_DETAILS()
                LANGUAGE plpgsql
                AS $$
                BEGIN
                    DROP TABLE IF EXISTS procurement_staff_details_from_store_procedure;
                    CREATE TEMP TABLE procurement_staff_details_from_store_procedure AS
                    SELECT 
                        ps.id AS staff_id,
                        ps.user_id,
                        u.name AS name,
                        ps.asset_type_id,
                        at.name AS asset_type_name,
                        ps.created_at,
                        ps.updated_at
                    FROM 
                        procurement_staff ps
                    JOIN 
                        users u ON ps.user_id = u.id
                    JOIN 
                        assets_types at ON ps.asset_type_id = at.id;
                END;
                $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_GET_PROCUREMENT_STAFF_DETAILS');
    }
};