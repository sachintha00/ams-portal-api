<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{    
    public function up(): void
    {
        Schema::create('procurement_staff', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->unsignedBigInteger('asset_type_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Define composite unique key
            $table->unique(['asset_type_id', 'user_id']);

            // Foreign key constraints
            $table->foreign('asset_type_id')->references('id')->on('assets_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_staff');
    }
};
