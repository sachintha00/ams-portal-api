<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_REMOVE_SUPPLIER(
                p_supplier_id bigint
            ) LANGUAGE plpgsql
            AS $$
            BEGIN
                IF p_supplier_id IS NULL OR p_supplier_id = 0 THEN
                    RAISE EXCEPTION 'Supplier ID cannot be null or zero';
                END IF;

                IF NOT EXISTS (SELECT 1 FROM supplair WHERE id = p_supplier_id) THEN
                    RAISE EXCEPTION 'supplier ID % does not exist', p_supplier_id;
                END IF;

                DELETE FROM supplair WHERE id = p_supplier_id;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_REMOVE_SUPPLIER');
    }
};