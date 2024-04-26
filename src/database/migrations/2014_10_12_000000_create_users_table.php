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
            $table->string('contact_person')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_email_verified')->default(0);
            $table->integer('employee_code')->nullable();
            $table->string('security_question')->nullable();
            $table->string('security_answer')->nullable();
            $table->string('activation_code')->nullable();
            $table->boolean('is_user_blocked')->nullable();
            $table->boolean('is_trial_account')->default(false);
            $table->timestamp('first_login')->nullable();
            $table->string('user_description')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->string('created_user')->nullable();
            $table->string('tenant_db_name')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};