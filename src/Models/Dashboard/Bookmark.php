<?php

namespace Nhd\Foundation\Models\Dashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'title',
        'url'
    ];
}
