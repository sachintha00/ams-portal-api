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
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_SIDEBAR_WITH_PERMISSION( 
                IN p_user_id INT DEFAULT NULL
            )
            AS $$
            BEGIN
                DROP TABLE IF EXISTS sidebar_item_from_store_procedure;
            
                IF p_user_id IS NOT NULL AND p_user_id <= 0 THEN
                    RAISE EXCEPTION 'Invalid p_user_id: %', p_user_id;
                END IF;
            
                CREATE TEMP TABLE sidebar_item_from_store_procedure AS
                SELECT
                    tbl.id,
                    tbl.permission_id,
                    tbl.parent_id,
                    tbl.menuname,
                    tbl.menulink,
                    tbl.icon
                FROM
                    users u
                INNER JOIN
                    model_has_roles mhr ON u.id = mhr.model_id
                INNER JOIN
                    roles r ON mhr.role_id = r.id
                INNER JOIN
                    role_has_permissions rhp ON r.id = rhp.role_id
                INNER JOIN
                    permissions p ON rhp.permission_id = p.id
                INNER JOIN
                    tbl_menu tbl ON tbl.permission_id = p.id
                WHERE
                    u.id = p_user_id OR p_user_id IS NULL OR p_user_id = 0
                GROUP BY
                tbl.id, tbl.permission_id, tbl.parent_id, tbl.menuname, tbl.menulink, tbl.icon;
            END;
            $$ LANGUAGE plpgsql;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_procedure_sidebar_item_with_permission_retrieve');
    }
};
