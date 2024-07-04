<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layout_widgets', function (Blueprint $table) {
            $table->id();
            $table->double('x');
            $table->double('y');
            $table->double('w');
            $table->double('h');
            $table->text('style');
            $table->boolean('status')->default(true);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('widget_id')->nullable();
            $table->string('widget_type')->nullable();

            $table->foreign('widget_id')->references('id')->on('widgets')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layout_widgets');
    }
};