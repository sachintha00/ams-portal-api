<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_request_queue_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('workflow_node_id');
            $table->unsignedInteger('workflow_level');
            $table->char('request_status_from_level', 10);
            $table->unsignedInteger('workflow_auth_order');
            $table->unsignedInteger('workflow_type');
            $table->unsignedBigInteger('approver_user_id');
            $table->string('comment_for_action', 50)->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('workflow_request_queues')->onDelete('cascade');
            $table->foreign('approver_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_request_queue_details');
    }
};