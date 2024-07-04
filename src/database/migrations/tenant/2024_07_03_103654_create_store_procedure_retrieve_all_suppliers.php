<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_SUPPLIER(
                IN p_supplier_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS suppliers_from_store_procedure;

                CREATE TEMP TABLE suppliers_from_store_procedure AS
                SELECT * FROM
                    supplair 
                WHERE
                    (supplair.id = p_supplier_id OR p_supplier_id IS NULL OR p_supplier_id = 0)
	            AND supplair.supplier_reg_status = 'APPROVED'
                ORDER BY supplair.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_SUPPLIER');
    }
};