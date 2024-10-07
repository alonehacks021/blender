<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class OrganizationalPost extends Model
{
    protected $fillable = [
        'id',
        'code',
        'title',
    ];

    public function organizationPostRoles() {
        return $this->hasMany(OrganizationalPostRole::class, 'organizational_post_id');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'organizational_post_roles');
    }
}
