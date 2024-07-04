<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplair', function (Blueprint $table) {
            $table->json('supplier_asset_classes')->nullable();
            $table->bigInteger('supplier_rating')->nullable();
            $table->string('supplier_bussiness_name')->nullable();
            $table->string('supplier_bussiness_register_no')->nullable();
            $table->string('supplier_primary_email')->nullable();
            $table->string('supplier_secondary_email')->nullable();
            $table->string('supplier_br_attachment')->nullable();
            $table->string('supplier_website')->nullable();
            $table->string('supplier_tel_no')->nullable();
            $table->string('supplier_mobile')->nullable();
            $table->string('supplier_fax')->nullable();
            $table->string('supplier_city')->nullable();
            $table->string('supplier_location_latitude')->nullable();
            $table->string('supplier_location_longitude')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('supplair', function (Blueprint $table) {
            $table->dropColumn('supplier_asset_classes');
            $table->dropColumn('supplier_rating');
            $table->dropColumn('supplier_bussiness_name');
            $table->dropColumn('supplier_bussiness_register_no');
            $table->dropColumn('supplier_primary_email');
            $table->dropColumn('supplier_secondary_email');
            $table->dropColumn('supplier_br_attachment');
            $table->dropColumn('supplier_website');
            $table->dropColumn('supplier_tel_no');
            $table->dropColumn('supplier_mobile');
            $table->dropColumn('supplier_fax');
            $table->dropColumn('supplier_city');
            $table->dropColumn('supplier_location_latitude');
            $table->dropColumn('supplier_location_longitude');
        });
    }
};
