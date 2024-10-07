<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class NahadGroup extends Model
{
    protected $fillable = [
        'nahad_id',
        'nahad_parent_id',
        'type',
        'title',
    ];

    public function parent() {
        return $this->belongsTo(self::class, 'nahad_parent_id');
    }

    public const TYPE_VICE = 1;
    public const TYPE_MANAGEMENT = 2;
    public const TYPE_OFFICE = 3;
}
