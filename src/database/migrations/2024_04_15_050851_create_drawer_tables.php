<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_drawer_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->timestamps();
        });

        Schema::create('table_drawer_item_lists', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->unsignedBigInteger('category_id');
            $table->json('design_obj');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('table_drawer_categories')->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('table_drawer_icons_list');
        Schema::dropIfExists('table_drawer_categories');
    }
};