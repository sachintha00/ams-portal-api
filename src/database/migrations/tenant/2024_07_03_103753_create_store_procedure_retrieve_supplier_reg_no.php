<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_SUPPLIER_REG_NO() LANGUAGE plpgsql
            AS $$
            DECLARE
                curr_val INT;
                next_supplier_id TEXT;
            BEGIN
                DROP TABLE IF EXISTS supplier_reg_no_from_store_procedure;
                CREATE TABLE supplier_reg_no_from_store_procedure(
                    supplier_reg_no TEXT
                );

                curr_val := last_value FROM supplier_id_seq;
                next_supplier_id := 'SUPPLIER-' || LPAD((curr_val)::TEXT, 4, '0');

                INSERT INTO supplier_reg_no_from_store_procedure
                    (supplier_reg_no) VALUES (next_supplier_id);

                RAISE INFO '%', next_supplier_id;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_SUPPLIER_REG_NO');
    }
};
