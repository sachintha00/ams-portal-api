<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('request_id');
            $table->unsignedBigInteger('procurement_by');
            $table->date('date')->nullable();
            $table->jsonb('selected_items')->nullable();
            $table->jsonb('selected_suppliers')->nullable();
            $table->jsonb('rpf_document')->nullable();
            $table->jsonb('attachment')->nullable();
            $table->date('required_date')->nullable();
            $table->string('comment')->nullable();
            $table->string('procurement_status')->nullable();
            $table->timestamps();
            
            $table->foreign('procurement_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};