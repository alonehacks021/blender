<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'name',
        'nick_name',
        'namespace',
        'order'
    ];

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
