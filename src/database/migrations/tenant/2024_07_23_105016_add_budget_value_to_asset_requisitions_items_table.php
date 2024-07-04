<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_requisitions_items', function (Blueprint $table) {
            $table->integer('requested_budget')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('asset_requisitions_items', function (Blueprint $table) {
            $table->dropColumn('requested_budget');
        });
    }
};