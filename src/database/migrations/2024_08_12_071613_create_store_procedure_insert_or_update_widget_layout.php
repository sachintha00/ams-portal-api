<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_INSERT_OR_UPDATE_DASHBOARD_LAYOUT_WIDGET(
                p_x DOUBLE PRECISION,
                p_y DOUBLE PRECISION,
                p_w DOUBLE PRECISION,
                p_h DOUBLE PRECISION,
                p_style TEXT,
                p_widget_id BIGINT,
                p_widget_type TEXT,
                p_id BIGINT DEFAULT NULL
            ) LANGUAGE plpgsql
            AS $$
            DECLARE
                return_layout_id BIGINT;
            BEGIN
                DROP TABLE IF EXISTS dashboard_layout_widget_response_from_store_procedure;
                CREATE TEMP TABLE dashboard_layout_widget_response_from_store_procedure (
                    status TEXT,
                    message TEXT,
                    widget_id BIGINT DEFAULT 0
                );

                BEGIN
                    IF p_id IS NULL OR p_id = 0 THEN
                    
                        SELECT id INTO return_layout_id
                        FROM layout_widgets
                        WHERE x = p_x AND y = p_y AND w = p_w AND h = p_h AND style = p_style;

                        IF NOT FOUND THEN
                            INSERT INTO layout_widgets 
                            (
                                x, y, w, h, style, widget_id, widget_type, created_at, updated_at
                            ) VALUES 
                            (
                                p_x, p_y, p_w, p_h, p_style, p_widget_id, p_widget_type, NOW(), NOW()
                            ) RETURNING id INTO return_layout_id;

                            INSERT INTO dashboard_layout_widget_response_from_store_procedure (status, message, widget_id)
                            VALUES ('SUCCESS', 'Widget added successfully', return_layout_id);
                        ELSE
                            UPDATE layout_widgets
                            SET 
                                updated_at = NOW()
                            WHERE id = return_layout_id;

                            INSERT INTO dashboard_layout_widget_response_from_store_procedure (status, message, widget_id)
                            VALUES ('SUCCESS', 'Widget already exists and was updated', return_layout_id);
                        END IF;
                    ELSE
                        UPDATE layout_widgets
                        SET 
                            x = p_x,
                            y = p_y,
                            w = p_w,
                            h = p_h,
                            style = p_style,
                            widget_id = p_widget_id,
                            widget_type = p_widget_type,
                            updated_at = NOW()
                        WHERE id = p_id RETURNING id INTO return_layout_id;

                        INSERT INTO dashboard_layout_widget_response_from_store_procedure (status, message, widget_id)
                            VALUES ('SUCCESS', 'Widget updated successfully', return_layout_id);

                        IF NOT FOUND THEN
                            INSERT INTO layout_widgets 
                            (
                                x, y, w, h, style, widget_id, widget_type, created_at, updated_at
                            ) VALUES 
                            (
                                p_x, p_y, p_w, p_h, p_style, p_widget_id, p_widget_type, NOW(), NOW()
                            ) RETURNING id INTO return_layout_id;

                            INSERT INTO dashboard_layout_widget_response_from_store_procedure (status, message, widget_id)
                            VALUES ('SUCCESS', 'Widget added successfully', return_layout_id);
                        END IF;
                    END IF;

                EXCEPTION WHEN OTHERS THEN
                    INSERT INTO dashboard_layout_widget_response_from_store_procedure (status, message, widget_id)
                    VALUES ('ERROR', SQLERRM, NULL);
                END;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_INSERT_OR_UPDATE_DASHBOARD_LAYOUT_WIDGET');
    }
};