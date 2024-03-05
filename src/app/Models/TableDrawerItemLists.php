<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableDrawerItemLists extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path', 'category_id', 'design_obj',
    ];

    protected $casts = [
        'design_obj' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(TableDrawerCategories::class, 'category_id');
    }
}