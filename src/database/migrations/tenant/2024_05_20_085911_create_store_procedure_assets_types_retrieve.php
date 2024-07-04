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
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_ASSEST_TYPES( 
                IN p_assest_type_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS assest_type_from_store_procedure;
            
                IF p_assest_type_id IS NOT NULL AND p_assest_type_id <= 0 THEN
                    RAISE EXCEPTION 'Invalid p_assest_type_id: %', p_assest_type_id;
                END IF;
            
                CREATE TEMP TABLE assest_type_from_store_procedure AS
                SELECT
                    a.id AS assest_type_id,
                    a.name,
                    a.description,
                    a.created_at,
                    a.updated_at
                FROM
                    assets_Types a
                WHERE
                    a.id = p_assest_type_id OR p_assest_type_id IS NULL OR p_assest_type_id = 0;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_ASSEST_TYPES');
    }
};
