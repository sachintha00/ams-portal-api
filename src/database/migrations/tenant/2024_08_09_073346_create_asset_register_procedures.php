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
            // // SQL statement to create the stored procedure
            // $procedure = <<<SQL
            //     CREATE OR REPLACE PROCEDURE create_full_asset_register(
            //         -- IN p_model_number VARCHAR(255),
            //         -- IN p_serial_number VARCHAR(255),
            //         IN p_thumbnail_image VARCHAR(255),
            //         IN p_qr_code VARCHAR(255),
            //         IN p_register_date DATETIME,
            //         IN p_assets_type BIGINT,
            //         IN p_category BIGINT,
            //         IN p_sub_category BIGINT,
            //         IN p_assets_value DECIMAL(10, 2),
            //         IN p_assets_document JSON,
            //         IN p_supplier BIGINT,
            //         IN p_purchase_order_number VARCHAR(255),
            //         IN p_purchase_cost DECIMAL(10, 2),
            //         IN p_purchase_type BIGINT,
            //         IN p_received_condition VARCHAR(255),
            //         IN p_warranty VARCHAR(255),
            //         IN p_other_purchase_details TEXT,
            //         IN p_purchase_document JSON,
            //         IN p_insurance_number VARCHAR(255),
            //         IN p_insurance_document JSON,
            //         IN p_expected_life_time VARCHAR(255),
            //         IN p_depreciation_value DECIMAL(5, 2),
            //         -- IN p_responsible_person BIGINT,
            //         -- IN p_location VARCHAR(255),
            //         -- IN p_department BIGINT,
            //         IN p_registered_by BIGINT,
            //         IN p_deleted BOOLEAN,
            //         IN p_deleted_at DATETIME,
            //         IN p_deleted_by BIGINT,
            //         IN p_assest_details JSON
            //     )
            //     LANGUAGE plpgsql
            //     AS \$\$
            //     DECLARE
            //         assest_details JSON;
            //     BEGIN
            //         -- -- Insert the asset requisition
            //         -- INSERT INTO asset_requisitions (requisition_id, requisition_by, requisition_date, requisition_status, created_at, updated_at)
            //         -- VALUES (_requisition_id, _user_id, _requisition_date, _requisition_status, _current_time, _current_time)
            //         -- RETURNING id INTO asset_requisition_id;
                
            //         -- Loop through the items JSON array and insert each item
            //         FOR assest_details IN SELECT * FROM json_array_elements(p_assest_details)
            //         LOOP
            //             INSERT INTO assets (
            //                 model_number,
            //                 serial_number,
            //                 thumbnail_image,
            //                 qr_code,
            //                 register_date,
            //                 assets_type,
            //                 category,
            //                 sub_category,
            //                 assets_value,
            //                 assets_document,
            //                 supplier,
            //                 purchase_order_number,
            //                 purchase_cost,
            //                 purchase_type,
            //                 received_condition,
            //                 warranty,
            //                 other_purchase_details,
            //                 purchase_document,
            //                 insurance_number,
            //                 insurance_document,
            //                 expected_life_time,
            //                 depreciation_value,
            //                 responsible_person,
            //                 location,
            //                 department,
            //                 registered_by,
            //                 deleted,
            //                 deleted_at,
            //                 deleted_by
            //             )
            //             VALUES (
            //                 assest_details->>'modelNumber', 
            //                 assest_details->>'serialNumber', 
            //                 p_thumbnail_image,
            //                 p_qr_code,
            //                 p_register_date,
            //                 p_assets_type,
            //                 p_category,
            //                 p_sub_category,
            //                 p_assets_value,
            //                 p_assets_document,
            //                 p_supplier,
            //                 p_purchase_order_number,
            //                 p_purchase_cost,
            //                 p_purchase_type,
            //                 p_received_condition,
            //                 p_warranty,
            //                 p_other_purchase_details,
            //                 p_purchase_document,
            //                 p_insurance_number,
            //                 p_insurance_document,
            //                 p_expected_life_time,
            //                 p_depreciation_value,
            //                 p_responsible_person,
            //                 assest_details->>'storedLocation',
            //                 (assest_details->>'organization')::BIGINT,
            //                 p_registered_by,
            //                 p_deleted,
            //                 p_deleted_at,
            //                 p_deleted_by
            //             );
            //         END LOOP;
            //     END;
            //     \$\$;
            //     SQL;
                
            // // Execute the SQL statement
            // DB::unprepared($procedure);

            $procedure = <<<SQL
                            CREATE OR REPLACE PROCEDURE create_full_asset_register(
                                IN p_thumbnail_image JSONB,
                                IN p_register_date TIMESTAMP,
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
                                IN p_registered_by BIGINT,
                                IN p_deleted BOOLEAN,
                                IN p_deleted_at TIMESTAMP,
                                IN p_deleted_by BIGINT,
                                IN p_asset_details JSONB
                            )
                            LANGUAGE plpgsql
                            AS \$\$
                            DECLARE
                                asset_detail JSONB;
                            BEGIN
                                -- Loop through the items JSON array and insert each item
                                FOR asset_detail IN SELECT * FROM jsonb_array_elements(p_asset_details)
                                LOOP
                                    INSERT INTO assets (
                                        model_number,
                                        serial_number,
                                        thumbnail_image,
                                        qr_code,
                                        register_date,
                                        assets_type,
                                        category,
                                        sub_category,
                                        assets_value,
                                        assets_document,
                                        supplier,
                                        purchase_order_number,
                                        purchase_cost,
                                        purchase_type,
                                        received_condition,
                                        warranty,
                                        other_purchase_details,
                                        purchase_document,
                                        insurance_number,
                                        insurance_document,
                                        expected_life_time,
                                        depreciation_value,
                                        responsible_person,
                                        location,
                                        department,
                                        registered_by,
                                        deleted,
                                        deleted_at,
                                        deleted_by
                                    )
                                    VALUES (
                                        asset_detail->>'modelNumber', 
                                        asset_detail->>'serialNumber', 
                                        p_thumbnail_image,
                                        asset_detail->>'p_qr_code',
                                        p_register_date,
                                        p_assets_type,
                                        p_category,
                                        p_sub_category,
                                        p_assets_value,
                                        p_assets_document,
                                        p_supplier,
                                        p_purchase_order_number,
                                        p_purchase_cost,
                                        p_purchase_type,
                                        p_received_condition,
                                        p_warranty,
                                        p_other_purchase_details,
                                        p_purchase_document,
                                        p_insurance_number,
                                        p_insurance_document,
                                        p_expected_life_time,
                                        p_depreciation_value,
                                        (asset_detail->>'selectedUser')::BIGINT,
                                        asset_detail->>'storedLocation',
                                        (asset_detail->>'organization')::BIGINT, 
                                        p_registered_by,
                                        p_deleted,
                                        p_deleted_at,
                                        p_deleted_by
                                    );
                                END LOOP;
                            END;
                            \$\$;
                        SQL;

                        // Execute the SQL statement
                        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS create_full_asset_register;');
    }
};
