<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('label', 255);
            $table->string('key', 255)->unique();
            $table->string('icon', 255)->nullable();
            $table->string('href', 255)->nullable();
            $table->integer('level')->default(1);

            $table->foreign('parent_id')->references('id')->on('sidebar_menus')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_menus');
    }
};