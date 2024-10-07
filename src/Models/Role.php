<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'owner_id',
        'name',
    ];

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function rolePermissions() {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    public function scopeFilter($query, $request) {
        if($request->has('name')) {
            $query->where('name', 'LIKE', '%'.str_replace(' ', '%', $request->get('name')).'%');
        }
    }

    public static function filters() {
        return [
            'name' => [
                'type' => 'text',
                'label' => 'نام/عنوان' 
            ]
        ];
    } 
}
