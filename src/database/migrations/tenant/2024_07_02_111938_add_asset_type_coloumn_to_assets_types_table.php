<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets_types', function (Blueprint $table) {
            $table->string('asset_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('assets_types', function (Blueprint $table) {
            $table->dropColumn('asset_type');
        });
    }
};
