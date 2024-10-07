<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nhd\Foundation\Foundation\Model\TimestampJalali;
use Illuminate\Database\Eloquent\Casts\Attribute;

class VerifyCode extends Model
{
    use TimestampJalali;
    
    protected $fillable = [
        'user_id',
        'attempts', 
        'checks',
        'hash',
        'code',
        'expired_at',
        'verified_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function verifiedAtJ(): Attribute {
        return Attribute::make(fn() => $this->verified_at ? jdate()->fromDateTime($this->verified_at)->format('Y/m/d H:i:s') : null);
    }

    public function expiredAtJ(): Attribute {
        return Attribute::make(fn() => jdate()->fromDateTime($this->expired_at)->format('Y/m/d H:i:s'));
    }
}
