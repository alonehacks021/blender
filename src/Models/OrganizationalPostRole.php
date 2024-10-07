<?php

namespace Nhd\Foundation\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class OrganizationalPostRole extends Model
{
    protected $fillable = [
        'organization_post_id',
        'role_id',
    ];
}
