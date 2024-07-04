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
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_ASSETS( 
                IN p_asset_id INT DEFAULT NULL
            ) 
            AS $$
            BEGIN
                DROP TABLE IF EXISTS assets_from_store_procedure;
            
                IF p_asset_id IS NOT NULL AND p_asset_id <= 0 THEN
                    RAISE EXCEPTION 'Invalid p_asset_id: %', p_asset_id;
                END IF;
            
                CREATE TEMP TABLE assets_from_store_procedure AS
                SELECT
                    a.id,
                    a.model_number,
                    a.serial_number,
                    a.thumbnail_image,
                    a.qr_code,
                    a.register_date,
                    a.assets_type as assets_type_id,
                    ast.name as assets_type_name,
                    a.category as category_id,
                    ac.name as category_name,
                    a.sub_category as sub_category_id,
                    assc.name as sub_category_name,
                    a.assets_value,
                    a.assets_document,
                    a.supplier as supplier_id,
                    s.name as supplier_name,
                    a.purchase_order_number,
                    a.purchase_cost,
                    a.purchase_type as purchase_type_id,
                    arat.name as purchase_type_name,
                    a.received_condition,
                    a.warranty,
                    a.other_purchase_details,
                    a.purchase_document,
                    a.insurance_number,
                    a.insurance_document,
                    a.expected_life_time,
                    a.depreciation_value,
                    a.responsible_person as responsible_person_id,
                    u.name as responsible_person_name,
                    a.location,
                    a.department as department_id,
                    o.data as department_data,
                    a.registered_by as registered_by_id,
                    ur.name as registered_by_name
                FROM
                    assets a
                INNER JOIN
                    assets_types ast ON a.assets_type = ast.id
                INNER JOIN
                    asset_categories ac ON a.category = ac.id
                INNER JOIN
                    asset_sub_categories assc ON a.sub_category = assc.id
                INNER JOIN
                    supplair s ON a.supplier = s.id
                INNER JOIN
                    assest_requisition_availability_type arat ON a.purchase_type = arat.id
                INNER JOIN
                    users u ON a.responsible_person = u.id
                INNER JOIN
                    organization o ON a.department = o.id
                INNER JOIN
                    users ur ON a.registered_by = ur.id
                WHERE
                    (a.id = p_asset_id OR p_asset_id IS NULL OR p_asset_id = 0)
                AND 
                    a.deleted = FALSE
                GROUP BY
                a.id, ast.id, ac.id, assc.id, s.id, arat.id, u.id, o.id, ur.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_ASSETS');
    }
};
