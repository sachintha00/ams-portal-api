<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_UPDATE_DATA(
                data_objs JSONB DEFAULT NULL, 
                unique_id TEXT DEFAULT NULL,
                unique_column TEXT DEFAULT 'id',
                p_table_name TEXT DEFAULT NULL
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                table_name TEXT;
                column_array TEXT[];
                update_statement TEXT;
                key TEXT;
            BEGIN
                IF data_objs IS NULL OR jsonb_typeof(data_objs) != 'object' THEN
                    RAISE EXCEPTION 'data_objs must be a non-null JSONB object';
                END IF;

                table_name := jsonb_object_keys(data_objs)::text;

                IF table_name IS NULL OR table_name = '' THEN
                    RAISE EXCEPTION 'Table name cannot be null or empty';
                END IF;

                IF unique_id IS NULL OR unique_id = '' THEN
                    RAISE EXCEPTION 'unique_id cannot be null or empty';
                END IF;

                SELECT ARRAY(SELECT jsonb_object_keys(data_objs->table_name)) INTO column_array;

                IF array_length(column_array, 1) IS NULL OR array_length(column_array, 1) = 0 THEN
                    RAISE EXCEPTION 'data_objs must contain at least one key-value pair';
                END IF;

                update_statement := '';
                FOREACH key IN ARRAY column_array LOOP
                    IF key != unique_column THEN
                        IF update_statement != '' THEN
                            update_statement := update_statement || ', ';
                        END IF;
                        update_statement := update_statement || key || ' = ';
                        update_statement := update_statement || quote_literal(data_objs->table_name->>key);
                    END IF;
                END LOOP;

                EXECUTE format('UPDATE public.%I SET %s WHERE %I::text = %L', table_name, update_statement, unique_column, unique_id);

                RAISE NOTICE 'Data updated in % table', table_name;

            EXCEPTION
                WHEN others THEN
                    RAISE EXCEPTION 'Error occurred: %', SQLERRM;
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_UPDATE_DATA');
    }
};