<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_WIDGETS()
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    result JSONB;
                BEGIN

                    DROP TABLE IF EXISTS temp_widgets_from_store_procedure;
                    CREATE TEMP TABLE temp_widgets_from_store_procedure (
                        data JSONB
                    );
                    
                    WITH widget_data AS (
                        SELECT
                            wc.category_name,
                            jsonb_build_object(
                                'id', w.id,
                                'image_path', w.image_path,
                                'design_obj', w.design_obj,
                                'design_component', w.design_component,
                                'widget_type', w.widget_type
                            ) AS widget_info
                        FROM
                            widgets w
                        JOIN
                            widgets_categories wc
                        ON
                            w.category_id = wc.id
                    ),
                    grouped_data AS (
                        SELECT
                            category_name,
                            jsonb_agg(widget_info) AS category_related_all_object
                        FROM
                            widget_data
                        GROUP BY
                            category_name
                    )
                    SELECT
                        jsonb_agg(
                            jsonb_build_object(
                                'category_name', category_name,
                                'category_related_all_object', category_related_all_object
                            )
                        )
                    INTO result
                    FROM
                        grouped_data;

                    INSERT INTO temp_widgets_from_store_procedure
                        (data) VALUES (result);
                    RAISE INFO '%', result;
                END;
                $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_WIDGETS');
    }
};