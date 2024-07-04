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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->jsonb('thumbnail_image')->nullable();
            $table->string('qr_code')->nullable();
            $table->dateTime('register_date');
            $table->unsignedBigInteger('assets_type');
            $table->unsignedBigInteger('category');
            $table->unsignedBigInteger('sub_category');
            $table->decimal('assets_value', 10, 2)->nullable();
            $table->jsonb('assets_document')->nullable();
            $table->unsignedBigInteger('supplier');
            $table->string('purchase_order_number')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->unsignedBigInteger('purchase_type');
            $table->string('received_condition')->nullable();
            $table->string('warranty')->nullable();
            $table->text('other_purchase_details')->nullable();
            $table->jsonb('purchase_document')->nullable();
            $table->string('insurance_number')->nullable();
            $table->jsonb('insurance_document')->nullable();
            $table->string('expected_life_time')->nullable();
            $table->decimal('depreciation_value', 5, 2)->nullable();
            $table->unsignedBigInteger('responsible_person');
            $table->string('location')->nullable();
            $table->unsignedBigInteger('department');
            $table->unsignedBigInteger('registered_by');
            $table->boolean('deleted')->default(false);
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('assets_type')->references('id')->on('assets_types')->onDelete('restrict');
            $table->foreign('category')->references('id')->on('asset_categories')->onDelete('restrict');
            $table->foreign('sub_category')->references('id')->on('asset_sub_categories')->onDelete('restrict');
            $table->foreign('supplier')->references('id')->on('supplair')->onDelete('restrict');
            $table->foreign('purchase_type')->references('id')->on('assest_requisition_availability_type')->onDelete('restrict');
            $table->foreign('responsible_person')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('department')->references('id')->on('organization')->onDelete('restrict');
            $table->foreign('registered_by')->references('id')->on('users')->onDelete('restrict'); // Prevents cascading delete
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
