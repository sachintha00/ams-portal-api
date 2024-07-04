<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_ITEM_TYPES( 
                IN p_item_type_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS item_type_from_store_procedure;
            
                IF p_item_type_id IS NOT NULL AND p_item_type_id <= 0 THEN
                    RAISE EXCEPTION 'Invalid p_item_type_id: %', p_item_type_id;
                END IF;
            
                CREATE TEMP TABLE item_type_from_store_procedure AS
                SELECT
                    it.id AS item_type_id,
                    it.name,
                    it.description,
                    it.created_at,
                    it.updated_at
                FROM
                    item_Types it
                WHERE
                    it.id = p_item_type_id OR p_item_type_id IS NULL OR p_item_type_id = 0;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_ITEM_TYPES');
    }
};
