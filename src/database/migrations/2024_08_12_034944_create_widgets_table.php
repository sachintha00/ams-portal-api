<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->unsignedBigInteger('category_id');
            $table->json('design_obj');
            $table->string('design_component');
            $table->string('widget_type');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('widgets_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};