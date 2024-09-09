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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_name')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('contact_Person_no')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->unsignedBigInteger('owner_user')->nullable();
            $table->boolean('is_tenant_blocked')->default(false);
            $table->boolean('is_trial_tenant')->default(false);
            $table->boolean('activate')->default(true);
            $table->string('activation_code')->nullable();
            $table->string('package')->nullable();
            $table->string('db_host')->nullable();
            $table->string('db_name')->nullable();
            $table->string('db_user')->nullable();
            $table->string('db_password')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('owner_user')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['owner_user']); // Drop foreign key constraint
            $table->dropForeign(['updated_by']); // Drop foreign key constraint
        });

        Schema::dropIfExists('tenants');
    }
};
