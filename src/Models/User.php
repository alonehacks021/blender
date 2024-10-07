<?php

namespace Nhd\Foundation\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Nhd\Foundation\Foundation\Model\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable, Filterable, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'username',
        'first_name',
        'last_name',
        'mobile',
        'gender',
        'birthday',
        'status',
        'type',
        'email',
        'image',
        'password',
        'ldap_login',
        'deleted_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        // 'birthday' => 'date'
    ];

    public function userRoles() {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id');
    }

    public function userChanges() {
        return $this->hasMany(UserChange::class, 'user_id');
    }

    public function verifyCodes() {
        return $this->hasMany(VerifyCode::class, 'user_id');
    }

    public function sessions(): HasMany {
        return $this->hasMany(Session::class, 'user_id');
    }

    public function getGenderTextAttribute() {
        return $this->gender ? trans('auth::consts.user_genders.' . $this->gender) : '---';
    }

    public function getGenderAdjectiveTextAttribute() {
        return $this->gender ? trans('auth::consts.user_genders_adjective.' . $this->gender) : '';
    }

    public function getTypeTextAttribute() {
        return trans('auth::consts.user_types.' . $this->type);
    }

    public function getFullNameAttribute() {
        return $this->first_name.' '.$this->last_name;
    }

    public function getBirthdayJAttribute() {
        return $this->birthday ? jdate()->fromDatetime($this->birthday)->format('Y/m/d') : null;
    }

    public function getImageUrlAttribute() {
        return $this->image ? \Storage::disk('users-images')->url($this->id) . '?rev=' . md5($this->image) : $this->getNoneImageUrl();
    }

    public function getImageContentAttribute() {
        return \Storage::disk('users-images')
            ->get($this->image);
    }

    public function getThumbnailUrlAttribute() {
        return $this->image ? \Storage::disk('users-images')->url($this->id . '?type=thumbnail&rev=' . md5($this->image)) : $this->getNoneThumbnailUrl();
    }

    public function getThumbnailPathAttribute() {
        $field = str_replace('.', '-thumb.', $this->image);
        $storage = \Storage::disk('users-images');

        if($storage->exists($field)) {
            return $storage->path($field);
        }

        return null;
    }

    public function getImagePathAttribute() {
        return $this->image ? \Storage::disk('users-images')->path($this->image) : null;
    }

    public function getNoneImageUrl() {
        return $this->isFemale() ? asset('vendor/auth/images/female-medium.png') : asset('vendor/auth/images/male-medium.png');
    }

    public function getNoneThumbnailUrl() {
        return $this->isFemale() ? asset('vendor/auth/images/female-small.png') : asset('vendor/auth/images/male-small.png');
    }

    public function getNoneImagePath() {
        return $this->isMale() ? ('vendor/auth/images/male-medium.png') : ('vendor/auth/images/female-medium.png');
    }

    public function getNoneThumbnailPath() {
        return $this->isMale() ? ('vendor/auth/images/male-small.png') : ('vendor/auth/images/female-small.png');
    }

    public function isAdmin() {
        return $this->type == self::TYPE_ADMIN;
    }

    public function isUser() {
        return $this->type == self::TYPE_USER;
    }

    public function isTemp() {
        return $this->type == self::TYPE_TEMP;
    }

    public function isActive() {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function isBan() {
        return $this->status == self::STATUS_BAN;
    }

    public function isFemale(){
        return $this->gender == self::GENDER_FEMALE;
    }

    public function isMale(){
        return $this->gender == self::GENDER_MALE;
    }

    public const STATUS_ACTIVE = 1;
    public const STATUS_BAN = 2;
    public static function getStatuses() {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_BAN
        ];
    }

    public const TYPE_ADMIN = 1;
    public const TYPE_USER = 2;
    public const TYPE_TEMP = 3;
    public static function getTypes() {
        return [
            self::TYPE_ADMIN,
            self::TYPE_USER,
        ];
    }

    public const GENDER_MALE = 1;
    public const GENDER_FEMALE = 2;
    public static function getGenders() {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE
        ];
    }

    public static function filters() {
        return [
            'username' => [
                'type' => 'text',
                'label' => 'نام کاربری' 
            ],
            'first_name' => [
                'type' => 'text',
                'label' => 'نام' 
            ],
            'last_name' => [
                'type' => 'text',
                'label' => 'نام خانوادگی' 
            ],
            'mobile' => [
                'type' => 'text',
                'label' => 'موبایل' 
            ],
            'email' => [
                'type' => 'text',
                'label' => 'ایمیل' 
            ],
            'gender' => [
                'type' => 'select',
                'label' => 'جنسیت',
                'items' => trans('auth::consts.user_genders'),
            ],
            'type' => [
                'type' => 'select',
                'label' => 'نوع',
                'items' => trans('auth::consts.user_types'),
            ],
            'status' => [
                'type' => 'select',
                'label' => 'وضعیت',
                'items' => trans('auth::consts.user_statuses'),
            ],
            'userRoles__role_id' => [
                'type' => 'ajax',
                'label' => 'نقش',
                'url' => '/dashboard/ajax/auth/roles-select2',
                'model' => Role::class,
                'text' => 'name',
            ]
        ];
    } 
}
