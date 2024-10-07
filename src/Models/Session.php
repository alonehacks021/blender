<?php

namespace Nhd\Foundation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nhd\Foundation\Foundation\Model\Filterable;

class Session extends Model
{
    use Filterable;

    public $timestamps = false;

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function id(): Attribute {
        return Attribute::make(fn() => $this->attributes['id']);
    }

    public function lastActivity(): Attribute {
        return Attribute::make(fn() => jdate()->fromCarbon(Carbon::createFromTimestamp($this->attributes['last_activity']))->format('%A Y/m/d H:i:s'));
    }

    public static function filters() {
        return [
            'user__username' => [
                'type' => 'text',
                'label' => 'نام کاربری' 
            ],
            'user__first_name' => [
                'type' => 'text',
                'label' => 'نام' 
            ],
            'user__last_name' => [
                'type' => 'text',
                'label' => 'نام خانوادگی' 
            ],
            'user__mobile' => [
                'type' => 'text',
                'label' => 'موبایل' 
            ],
            'user__gender' => [
                'type' => 'select',
                'label' => 'جنسیت',
                'items' => trans('auth::consts.user_genders'),
            ],
            'user__type' => [
                'type' => 'select',
                'label' => 'نوع',
                'items' => trans('auth::consts.user_types'),
            ],
        ];
    } 
}