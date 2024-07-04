<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_INSERT_OR_UPDATE_PROCUREMENT_STAFF(
                p_user_id BIGINT,
                p_asset_type_id BIGINT,
                p_id BIGINT DEFAULT NULL
            ) LANGUAGE plpgsql
            AS $$
            DECLARE
                return_staff_id BIGINT;
            BEGIN
                DROP TABLE IF EXISTS procurement_staff_response_from_store_procedure;
                CREATE TEMP TABLE procurement_staff_response_from_store_procedure (
                    status TEXT,
                    message TEXT,
                    staff_id BIGINT DEFAULT 0
                );

                BEGIN
                    IF p_id IS NULL OR p_id = 0 THEN
                        INSERT INTO procurement_staff 
                        (
                            user_id, asset_type_id, created_at, updated_at
                        ) VALUES 
                        (
                            p_user_id, p_asset_type_id, NOW(), NOW()
                        ) RETURNING id INTO return_staff_id;

                        INSERT INTO procurement_staff_response_from_store_procedure (status, message, staff_id)
                        VALUES ('SUCCESS', 'Staff Added successfully', return_staff_id);
                    ELSE
                        UPDATE procurement_staff
                        SET 
                            user_id = p_user_id,
                            asset_type_id = p_asset_type_id,
                            updated_at = NOW()
                        WHERE id = p_id RETURNING id INTO return_staff_id;

                        INSERT INTO procurement_staff_response_from_store_procedure (status, message, staff_id)
                            VALUES ('SUCCESS', 'Staff updated successfully', return_staff_id);

                        IF NOT FOUND THEN
                            INSERT INTO procurement_staff 
                            (
                                user_id, asset_type_id, created_at, updated_at
                            ) VALUES (
                                p_user_id, p_asset_type_id, NOW(), NOW()
                            ) RETURNING id INTO return_staff_id;

                            INSERT INTO procurement_staff_response_from_store_procedure (status, message, staff_id)
                            VALUES ('SUCCESS', 'Staff Added successfully', return_staff_id);
                        END IF;
                    END IF;

                EXCEPTION WHEN OTHERS THEN
                    INSERT INTO procurement_staff_response_from_store_procedure (status, message, staff_id)
                    VALUES ('ERROR', SQLERRM, NULL);
                END;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_INSERT_OR_UPDATE_PROCUREMENT_STAFF');
    }
};