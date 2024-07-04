<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_DELETE_PROCUREMENT_STAFF(
                p_procurement_staff_id bigint
            ) LANGUAGE plpgsql
            AS $$
            BEGIN
                IF p_procurement_staff_id IS NULL OR p_procurement_staff_id = 0 THEN
                    RAISE EXCEPTION 'Procurement ID cannot be null or zero';
                END IF;

                IF NOT EXISTS (SELECT 1 FROM procurement_staff WHERE id = p_procurement_staff_id) THEN
                    RAISE EXCEPTION 'Procurement ID % does not exist', p_workflow_id;
                END IF;

                DELETE FROM procurement_staff WHERE id = p_procurement_staff_id;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_DELETE_PROCUREMENT_STAFF');
    }
};