<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_ASSEST_CATEGORIES( 
                IN p_asset_categories_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS asset_categories_from_store_procedure;

                IF p_asset_categories_id IS NOT NULL AND p_asset_categories_id <= 0 THEN
                    RAISE EXCEPTION 'Invalid p_asset_categories_id: %', p_asset_categories_id;
                END IF;

                CREATE TEMP TABLE asset_categories_from_store_procedure AS
                SELECT 
                    ac.id AS ac_id,
                    ac.name,
                    ac.description AS ac_description,
                    COALESCE(
                        json_agg(
                            json_build_object(
                                'assc_id', assc.id,
                                'asset_category_id', assc.asset_category_id,
                                'assc_name', assc.name,
                                'assc_description', assc.description
                            )
                        ) FILTER (WHERE assc.id IS NOT NULL), '[]'
                    ) AS sub_categories
                FROM
                    asset_categories ac
                LEFT JOIN
                    asset_sub_categories assc ON ac.id = assc.asset_category_id
                WHERE
                    ac.id = p_asset_categories_id OR p_asset_categories_id IS NULL
                GROUP BY
                    ac.id
                ORDER BY
                    ac.id;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_ASSEST_CATEGORIES');
    }
};
