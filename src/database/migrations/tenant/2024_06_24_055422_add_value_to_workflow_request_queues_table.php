<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValueToWorkflowRequestQueuesTable extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_request_queues', function (Blueprint $table) {
            $table->bigInteger('value')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('workflow_request_queues', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
}

