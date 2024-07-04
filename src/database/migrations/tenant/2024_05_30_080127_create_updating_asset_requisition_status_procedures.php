<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(
            'CREATE OR REPLACE PROCEDURE update_asset_requisition_status(
                IN _requisition_id VARCHAR(255),
                IN _requisition_status VARCHAR(255)
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                UPDATE asset_requisitions
                SET requisition_status = _status
                WHERE requisition_id = _requestId;

                COMMIT;
            END;
            $$;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS update_asset_requisition_status;');
    }
};
