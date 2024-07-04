<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_ASSET_TYPES(
                IN p_asset_type_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS asset_types_from_store_procedure;

                CREATE TEMP TABLE asset_types_from_store_procedure AS
                SELECT * FROM
                    assets_types 
                WHERE
                    assets_types.id = p_asset_type_id OR p_asset_type_id IS NULL OR p_asset_type_id = 0
                ORDER BY assets_types.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_ASSET_TYPES');
    }
};
