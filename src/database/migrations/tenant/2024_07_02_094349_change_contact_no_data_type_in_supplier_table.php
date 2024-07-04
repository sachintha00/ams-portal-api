<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplair', function (Blueprint $table) {
            $table->dropColumn('contact_no');
        });

        Schema::table('supplair', function (Blueprint $table) {
            $table->json('contact_no')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('supplair', function (Blueprint $table) {
            $table->dropColumn('contact_no');
        });

        Schema::table('supplair', function (Blueprint $table) {
            $table->string('contact_no')->nullable();
        });
    }
};
