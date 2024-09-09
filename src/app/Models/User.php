<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'user_name',
        'email',
        'name',
        'contact_no',
        'profile_image',
        'contact_person',
        'website',
        'address',
        'email_verified_at',
        'is_email_verified',
        'password',
        'employee_code',
        'security_question',
        'security_answer',
        'activation_code',
        'is_user_blocked',
        'first_login',
        'user_description',
        'status',
        'is_owner',
        'is_app_user',
        'created_by',
        'tenant_id'
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

    public function tenants()
    {
        return $this->belongsTo(tenants::class);
    }
}