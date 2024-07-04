<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LayoutItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'x', 'y', 'w', 'h', 'style', 'status'
    ];

    protected $dates = ['deleted_at'];
}