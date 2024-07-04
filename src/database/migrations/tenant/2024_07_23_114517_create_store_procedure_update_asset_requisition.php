<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_UPDATE_ASSET_REQUISITION(
                p_selected_items JSONB
            ) LANGUAGE plpgsql
            AS $$
            DECLARE
                item_record JSONB;
                return_id BIGINT;
            BEGIN
                FOR item_record IN
                    SELECT * FROM jsonb_array_elements(p_selected_items)
                LOOP
                    UPDATE asset_requisitions_items
                    SET
                        item_count = (item_record->>'quantity')::BIGINT,
                        requested_budget = (item_record->>'budget')::BIGINT
                    WHERE id = (item_record->>'id')::INT RETURNING id INTO return_id;
                END LOOP;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_UPDATE_ASSET_REQUISITION');
    }
};