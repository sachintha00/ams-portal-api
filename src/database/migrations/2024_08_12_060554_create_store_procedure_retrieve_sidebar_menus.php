<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_RETRIEVE_SIDEBAR_MENUS()
                AS $$
                DECLARE
                    record RECORD;
                BEGIN
                    DROP TABLE IF EXISTS temp_sidebar_menus_from_store_procedure;
                    
                    CREATE TEMP TABLE temp_sidebar_menus_from_store_procedure (
                        id INTEGER,
                        parent_id INTEGER,
                        label VARCHAR,
                        key VARCHAR,
                        icon VARCHAR,
                        href VARCHAR,
                        level INTEGER
                    );

                    INSERT INTO temp_sidebar_menus_from_store_procedure (id, parent_id, label, key, icon, href, level)
                    WITH RECURSIVE menu_tree AS (
                        SELECT mi.id, mi.parent_id, mi.label, mi.key, mi.icon, mi.href, mi.level
                        FROM sidebar_menus mi
                        WHERE mi.parent_id IS NULL 
                        UNION ALL
                        SELECT mi.id, mi.parent_id, mi.label, mi.key, mi.icon, mi.href, mi.level
                        FROM sidebar_menus mi
                        INNER JOIN menu_tree mt ON mi.parent_id = mt.id
                    )
                    SELECT mt.id, mt.parent_id, mt.label, mt.key, mt.icon, mt.href, mt.level
                    FROM menu_tree mt
                    ORDER BY mt.level, mt.parent_id, mt.id;

                    RAISE NOTICE 'Data in temp_menu_items:';
                    FOR record IN
                        SELECT * FROM temp_sidebar_menus_from_store_procedure
                    LOOP
                        RAISE NOTICE 'id: %, parent_id: %, label: %, key: %, icon: %, href: %, level: %',
                            record.id, record.parent_id, record.label, record.key, record.icon, record.href, record.level;
                    END LOOP;
                    
                END;
                $$ LANGUAGE plpgsql;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_RETRIEVE_SIDEBAR_MENUS');
    }
};