<?php

namespace Nhd\Foundation\Models\Dashboard;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'title',
        'data',
        'class',
    ];

    protected function data(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value),
        );
    }

    public function getCreatedAtJAttribute() {
        return jdate()->fromDateTime($this->created_at)->format('Y/m/d H:i');
    }
}
