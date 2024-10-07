<?php

namespace Nhd\Foundation\Models\Dashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    private static $options = [];

    protected $fillable = [
        'user_id',
        'name',
        'val'
    ];

    public static function set($name, $value, $userId = null) {
        self::updateOrCreate([
            'name' => $name,
            'user_id' => $userId,
        ], [
            'val' => $value
        ]);
    }

    public static function setArray($name, $value, $userId = null) {
        self::updateOrCreate([
            'name' => $name,
            'user_id' => $userId,
        ], [
            'val' => json_encode($value)
        ]);
    }

    public static function get($name, $defaultValue = null, $userId = null) {
        if(array_key_exists($name, self::$options)) {
            return self::$options[$name];
        }

        $option = self::where([
            'name' => $name,
            'user_id' => $userId
        ])->first();

        $value = $defaultValue;
        if($option) {
            $value = $option->val;
            self::$options[$name] = $value;
        }

        return $value;
    }

    public static function getArray($name, $defaultValue = [], $userId = null) {
        if(array_key_exists($name, self::$options)) {
            return is_string(self::$options[$name]) ? json_decode(self::$options[$name]) : self::$options[$name];
        }

        $option = self::where([
            'name' => $name,
            'user_id' => $userId
        ])->first();

        $value = $defaultValue;
        if($option) {
            $value = json_decode($option->val, true);
            self::$options[$name] = $value;
        }

        return $value;
    }

    public static function getAll($names, $userId = null) {
        self::$options = self::where('user_id', $userId)
            ->whereIn('name', $names)
            ->pluck('val', 'name')
            ->toArray();

        return self::$options;
    }
}
