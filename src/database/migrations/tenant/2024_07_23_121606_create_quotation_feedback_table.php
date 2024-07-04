<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationFeedbackTable extends Migration
{
    public function up()
    {
        Schema::create('quotation_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('procurement_id');
            $table->integer('selected_supplier_id');
            $table->jsonb('selected_items');
            $table->date('available_date');
            $table->unsignedBigInteger('feedback_fill_by');
            $table->timestamps();

            $table->foreign('feedback_fill_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotation_feedbacks');
    }
}