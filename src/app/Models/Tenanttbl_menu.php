<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Tenanttbl_menu extends Model
{
    use HasFactory;

    public $table = "tbl_menu";
  
    protected $fillable = [
        'permission_id',
        'parent_id',
        'RightsCode',
        'MenuTxtCode',
        'RightsCode',
        'MenuName',
        'Description',
        'path',
        'MenuLink',
        'MenuOrder',
        'Enabled',
        'MenuPath',
        'icon'
    ];

    public function children()
    {
        return $this->hasMany(Tenanttbl_menu::class, 'parent_id');
    }
    public function Permission()
    {
        return $this->hasMany(Permission::class);
    }
}