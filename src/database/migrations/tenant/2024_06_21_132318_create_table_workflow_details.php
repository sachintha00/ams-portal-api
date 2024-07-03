<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_detail_parent_id');
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('workflow_detail_type_id');
            $table->unsignedBigInteger('workflow_detail_behavior_type_id');
            $table->integer('workflow_detail_order');
            $table->integer('workflow_detail_level');
            $table->json('workflow_detail_data_object')->nullable();
            $table->timestamps();

            $table->foreign('workflow_id')->references('id')->on('workflows');
            $table->foreign('workflow_detail_type_id')->references('id')->on('workflow_types');
            $table->foreign('workflow_detail_behavior_type_id')->references('id')->on('workflow_behavior_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_details');
    }
};