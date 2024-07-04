<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_id', 255);
            $table->unsignedBigInteger('requisition_by');
            $table->foreign('requisition_by')->references('id')->on('users')->onDelete('cascade');
            $table->date('requisition_date');
            $table->string('requisition_status', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_requisitions');
    }
};
