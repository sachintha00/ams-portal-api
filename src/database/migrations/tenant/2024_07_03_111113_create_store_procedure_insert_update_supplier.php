<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_INSERT_OR_UPDATE_SUPPLIER(
                p_name VARCHAR(255),
                p_address VARCHAR(255),
                p_description TEXT,
                p_supplier_asset_classes JSON,
                p_supplier_rating BIGINT,
                p_supplier_bussiness_name VARCHAR(255),
                p_supplier_bussiness_register_no VARCHAR(50),
                p_supplier_primary_email VARCHAR(255),
                p_supplier_secondary_email VARCHAR(255),
                p_supplier_br_attachment VARCHAR(255),
                p_supplier_website VARCHAR(255),
                p_supplier_tel_no VARCHAR(50),
                p_supplier_mobile VARCHAR(50),
                p_supplier_fax VARCHAR(50),
                p_supplier_city VARCHAR(100),
                p_supplier_location_latitude VARCHAR(50),
                p_supplier_location_longitude VARCHAR(50),
                p_contact_no JSON,
                p_id BIGINT DEFAULT NULL,
                p_supplier_register_status TEXT DEFAULT 'PENDING'
            ) LANGUAGE plpgsql
            AS $$
            DECLARE
                curr_val INT;
                supplier_id TEXT;
                return_supplier_id BIGINT;
            BEGIN
                DROP TABLE IF EXISTS supplier_add_response_from_store_procedure;
                CREATE TEMP TABLE supplier_add_response_from_store_procedure (
                    status TEXT,
                    message TEXT,
                    supplier_id BIGINT DEFAULT 0
                );

                IF p_supplier_rating IS NULL THEN
                    RAISE EXCEPTION 'Supplier rating cannot be null';
                END IF;

                SELECT nextval('supplier_id_seq') INTO curr_val;
                supplier_id := 'SUPPLIER-' || LPAD(curr_val::TEXT, 4, '0');

                IF p_id IS NULL OR p_id = 0 THEN
                    INSERT INTO supplair (
                        name, address, description, created_at, updated_at,
                        supplier_asset_classes, supplier_rating, supplier_bussiness_name,
                        supplier_bussiness_register_no, supplier_primary_email, supplier_secondary_email,
                        supplier_br_attachment, supplier_website, supplier_tel_no, supplier_mobile,
                        supplier_fax, supplier_city, supplier_location_latitude, supplier_location_longitude,
                        contact_no, supplier_reg_no, supplier_reg_status
                    ) VALUES (
                        p_name, p_address, p_description, NOW(), NOW(),
                        p_supplier_asset_classes, p_supplier_rating, p_supplier_bussiness_name,
                        p_supplier_bussiness_register_no, p_supplier_primary_email, p_supplier_secondary_email,
                        p_supplier_br_attachment, p_supplier_website, p_supplier_tel_no, p_supplier_mobile,
                        p_supplier_fax, p_supplier_city, p_supplier_location_latitude, p_supplier_location_longitude,
                        p_contact_no, supplier_id, p_supplier_register_status
                    ) RETURNING id INTO return_supplier_id;

                    INSERT INTO supplier_add_response_from_store_procedure (status, message, supplier_id)
                    VALUES ('SUCCESS', 'Supplier Added successfully', return_supplier_id);
                ELSE
                    UPDATE supplair
                    SET 
                        name = p_name,
                        address = p_address,
                        description = p_description,
                        updated_at = NOW(),
                        supplier_asset_classes = p_supplier_asset_classes,
                        supplier_rating = p_supplier_rating,
                        supplier_bussiness_name = p_supplier_bussiness_name,
                        supplier_bussiness_register_no = p_supplier_bussiness_register_no,
                        supplier_primary_email = p_supplier_primary_email,
                        supplier_secondary_email = p_supplier_secondary_email,
                        supplier_br_attachment = p_supplier_br_attachment,
                        supplier_website = p_supplier_website,
                        supplier_tel_no = p_supplier_tel_no,
                        supplier_mobile = p_supplier_mobile,
                        supplier_fax = p_supplier_fax,
                        supplier_city = p_supplier_city,
                        supplier_location_latitude = p_supplier_location_latitude,
                        supplier_location_longitude = p_supplier_location_longitude,
                        contact_no = p_contact_no
                    WHERE id = p_id RETURNING id INTO return_supplier_id;

                    INSERT INTO supplier_add_response_from_store_procedure (status, message, supplier_id)
                        VALUES ('SUCCESS', 'Supplier updated successfully', return_supplier_id);
                    
                    IF NOT FOUND THEN
                        INSERT INTO supplair (
                            name, address, description, created_at, updated_at,
                            supplier_asset_classes, supplier_rating, supplier_bussiness_name,
                            supplier_bussiness_register_no, supplier_primary_email, supplier_secondary_email,
                            supplier_br_attachment, supplier_website, supplier_tel_no, supplier_mobile,
                            supplier_fax, supplier_city, supplier_location_latitude, supplier_location_longitude,
                            contact_no, supplier_reg_no, supplier_reg_status
                        ) VALUES (
                            p_name, p_address, p_description, NOW(), NOW(),
                            p_supplier_asset_classes, p_supplier_rating, p_supplier_bussiness_name,
                            p_supplier_bussiness_register_no, p_supplier_primary_email, p_supplier_secondary_email,
                            p_supplier_br_attachment, p_supplier_website, p_supplier_tel_no, p_supplier_mobile,
                            p_supplier_fax, p_supplier_city, p_supplier_location_latitude, p_supplier_location_longitude,
                            p_contact_no, supplier_id, p_supplier_register_status
                        ) RETURNING id INTO return_supplier_id;

                        INSERT INTO supplier_add_response_from_store_procedure (status, message, supplier_id)
                        VALUES ('SUCCESS', 'Supplier Added successfully', return_supplier_id);
                    END IF;
                END IF;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_INSERT_OR_UPDATE_SUPPLIER');
    }
};