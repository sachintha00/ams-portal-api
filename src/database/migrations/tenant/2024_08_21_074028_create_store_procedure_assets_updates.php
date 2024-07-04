<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(
            'CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_UPDATE_ASSETS(
                IN _asset_id bigint,
                IN p_model_number VARCHAR(255),
                IN p_serial_number VARCHAR(255),
                IN p_responsible_person BIGINT, 
                IN p_location VARCHAR(255),
                IN p_department BIGINT,
                IN p_thumbnail_image JSONB,
                IN p_assets_type BIGINT,
                IN p_category BIGINT,
                IN p_sub_category BIGINT,
                IN p_assets_value DECIMAL(10, 2),
                IN p_assets_document JSONB,
                IN p_supplier BIGINT,
                IN p_purchase_order_number VARCHAR(255),
                IN p_purchase_cost DECIMAL(10, 2),
                IN p_purchase_type BIGINT,
                IN p_received_condition VARCHAR(255),
                IN p_warranty VARCHAR(255),
                IN p_other_purchase_details TEXT,
                IN p_purchase_document JSONB,
                IN p_insurance_number VARCHAR(255),
                IN p_insurance_document JSONB,
                IN p_expected_life_time VARCHAR(255),
                IN p_depreciation_value DECIMAL(10, 2),
                IN p_updated_at TIMESTAMP
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                UPDATE assets
                SET 
                    model_number = p_model_number,
                    serial_number = p_serial_number,
                    thumbnail_image = p_thumbnail_image,
                    assets_type = p_assets_type,
                    category = p_category, 
                    sub_category = p_sub_category,
                    assets_value = p_assets_value,
                    assets_document = p_assets_document,
                    supplier = p_supplier,
                    purchase_order_number = p_purchase_order_number,
                    purchase_cost = p_purchase_cost,
                    purchase_type = p_purchase_type,
                    received_condition = p_received_condition,
                    warranty = p_warranty,
                    other_purchase_details = p_other_purchase_details,
                    purchase_document = p_purchase_document,
                    insurance_number = p_insurance_number,
                    insurance_document = p_insurance_document,
                    expected_life_time = p_expected_life_time,
                    depreciation_value = p_depreciation_value,
                    responsible_person = p_responsible_person,
                    location = p_location,
                    department = p_department,
                    updated_at = p_updated_at
                WHERE id = _asset_id;
            END; 
            $$;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_UPDATE_ASSETS');
    }
};
