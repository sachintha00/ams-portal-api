<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_DESIGNATIONS(
                IN p_designation_id INT DEFAULT NULL
            ) AS
            $$
                BEGIN
                    DROP TABLE IF EXISTS designations_from_store_procedure;
                
                    IF p_designation_id IS NOT NULL AND p_designation_id <= 0 THEN
                        RAISE EXCEPTION 'Invalid p_designation_id: %', p_designation_id;
                    END IF;
                
                    CREATE TEMP TABLE designations_from_store_procedure AS
                    SELECT * FROM designations 
                        WHERE designations.id = p_designation_id 
                        OR p_designation_id IS NULL 
                        OR p_designation_id = 0;
                END
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_DESIGNATIONS');
    }
};