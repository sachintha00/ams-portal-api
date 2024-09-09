<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->string('email')->unique(); 
            $table->string('name')->nullable();
            $table->string('contact_no');
            $table->string('profile_image')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_email_verified')->default(0);
            $table->string('password');
            $table->integer('employee_code')->nullable();
            $table->string('security_question')->nullable();
            $table->string('security_answer')->nullable();
            $table->string('activation_code')->nullable();
            $table->boolean('is_user_blocked')->default(false);
            $table->timestamp('first_login')->nullable();
            $table->string('user_description')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_app_user')->default(true);
            $table->string('created_by')->nullable(); 
            // $table->unsignedBigInteger('tenant_id')->nullable();
            // $table->unsignedBigInteger('designation_id')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            // $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};