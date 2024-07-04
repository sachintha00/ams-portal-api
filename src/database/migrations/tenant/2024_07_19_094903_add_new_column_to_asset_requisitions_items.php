<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_requisitions_items', function (Blueprint $table) {
            $table->integer('item_count')->nullable()->after('quantity');
        });
    }


    public function down(): void
    {
        Schema::table('asset_requisitions_items', function (Blueprint $table) {
            $table->dropColumn('item_count');
        });
    }
};