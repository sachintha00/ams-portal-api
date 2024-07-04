<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_GET_PREFIXES_RELATED_PROCESS(
                process_id INT DEFAULT 0,
                process_name TEXT DEFAULT NULL
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_prefix_type_id INT;
                result JSONB := '[]'::jsonb;
                prefix_record RECORD;
                incremented_next_id INT;
            BEGIN
                DROP TABLE IF EXISTS prefix_list_with_next_number_from_store_procedure;
                CREATE TEMP TABLE IF NOT EXISTS prefix_list_with_next_number_from_store_procedure (
                    prefixes_data JSONB
                );
                
                IF process_id != 0 THEN
                    SELECT id
                    INTO v_prefix_type_id
                    FROM public.prefix_types
                    WHERE id = process_id;

                    IF v_prefix_type_id IS NOT NULL THEN
                        FOR prefix_record IN
                            SELECT p.prefix, p.next_id
                            FROM public.prefixes p
                            WHERE p.prefix_type_id = v_prefix_type_id
                        LOOP
                            incremented_next_id := prefix_record.next_id + 1;
                            result := result || jsonb_build_object('prefix', prefix_record.prefix, 'next_id', incremented_next_id);
                        END LOOP;

                        RAISE NOTICE 'Result: %', result;
                    ELSE
                        RAISE NOTICE 'No record found with process_id %', process_id;
                    END IF;

                ELSIF process_name IS NOT NULL THEN
                    SELECT id
                    INTO v_prefix_type_id
                    FROM public.prefix_types
                    WHERE prefix_type_name = process_name;

                    IF v_prefix_type_id IS NOT NULL THEN
                        FOR prefix_record IN
                            SELECT p.prefix, p.next_id
                            FROM public.prefixes p
                            WHERE p.prefix_type_id = v_prefix_type_id
                        LOOP
                            incremented_next_id := prefix_record.next_id + 1;
                            result := result || jsonb_build_object('prefix', prefix_record.prefix, 'next_id', incremented_next_id);
                        END LOOP;

                        RAISE NOTICE 'Result: %', result;
                    ELSE
                        RAISE NOTICE 'No record found with process_name %', process_name;
                    END IF;

                ELSE
                    RAISE NOTICE 'Neither process_id nor process_name provided.';
                END IF;

                INSERT INTO prefix_list_with_next_number_from_store_procedure VALUES (result);

            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_GET_PREFIXES_RELATED_PROCESS');
    }
};