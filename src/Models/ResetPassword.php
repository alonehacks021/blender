<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class ResetPassword extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'mobile',
        'verify_id',
        'access_token',
        'expired_at',
    ];
}
