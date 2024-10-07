<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChange extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'created_at'
    ];

    public static function commit($type, $user = null) {
        self::firstOrCreate([
            'user_id' => ($user ? $user->id : auth()->user()->id),
            'type' => $type,
        ], [
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    public const TYPE_CHANGE_GENDER = 1;
}
