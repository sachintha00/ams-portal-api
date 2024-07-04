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
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_SUPPLAIR( 
                IN p_supplair_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS supplair_from_store_procedure;
            
                IF p_supplair_id IS NOT NULL AND p_supplair_id <= 0 THEN
                    RAISE EXCEPTION 'Invalid p_supplair_id: %', p_supplair_id;
                END IF;
            
                CREATE TEMP TABLE supplair_from_store_procedure AS
                SELECT
                    s.id AS supplair_id,
                    s.name,
                    s.contact_no,
                    s.address,
                    s.description,
                    s.created_at,
                    s.updated_at
                FROM
                    supplair s
                WHERE
                    s.id = p_supplair_id OR p_supplair_id IS NULL OR p_supplair_id = 0;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_SUPPLAIR');
    }
};
