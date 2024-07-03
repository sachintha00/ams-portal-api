<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_request_type_id');
            $table->string('workflow_name');
            $table->text('workflow_description')->nullable();
            $table->boolean('workflow_status')->default(true);
            $table->timestamps();

            $table->foreign('workflow_request_type_id')->references('id')->on('workflow_request_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};