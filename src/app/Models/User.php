<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'user_name',
        'email',
        'name',
        'contact_no',
        'contact_person',
        'website',
        'address',
        'password',
        'email_verified_at',
        'employee_code',
        'security_question',
        'security_answer',
        'activation_code',
        'is_user_blocked',
        'is_trial_account',
        'first_login',
        'user_description',
        'is_deleted',
        'is_owner',
        'created_user',
        'tenant_db_name'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_user_blocked' => 'boolean',
        'is_trial_account' => 'boolean',
        'first_login' => 'datetime',
        'is_deleted' => 'boolean',
        'is_owner' => 'boolean',
    ];
}