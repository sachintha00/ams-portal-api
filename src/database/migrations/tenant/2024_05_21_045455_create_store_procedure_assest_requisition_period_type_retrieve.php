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
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_PERIOD_TYPES( 
                IN p_period_type_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS period_type_from_store_procedure;
            
                IF p_period_type_id IS NOT NULL AND p_period_type_id <= 0 THEN
                    RAISE EXCEPTION 'Invalid p_period_type_id: %', p_period_type_id;
                END IF;
            
                CREATE TEMP TABLE period_type_from_store_procedure AS
                SELECT
                    arpt.id AS period_type_id,
                    arpt.name,
                    arpt.description,
                    arpt.created_at,
                    arpt.updated_at
                FROM
                    assest_requisition_period_type arpt
                WHERE
                    arpt.id = p_period_type_id OR p_period_type_id IS NULL OR p_period_type_id = 0;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_PERIOD_TYPES');
    }
};
