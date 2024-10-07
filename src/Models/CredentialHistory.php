<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CredentialHistory extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'type',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function verifiedAtJ(): Attribute {
        return Attribute::make(fn() => $this->verified_at ? jdate()->fromDateTime($this->verified_at)->format('Y/m/d H:i:s') : null);
    }

    public const TYPE_QR = 1;
    public const TYPE_PASSWORD = 2;
}
