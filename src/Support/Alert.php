<?php

namespace Nhd\Foundation\Support;

class Alert {
    private static $messages = [];
    private static $channels = [];

    public static function add($message, $type) {
        self::$messages[] = [
            'message' => $message,
            'type' => $type
        ];

        session()->flash('alerts', self::$messages);
    }

    public static function all() {
        return session()->get('alerts', []);
    }

    public static function count() {
        return count(session()->get('alerts', []));
    }

    public static function addToChannel($channel, $message, $type) {
        self::$channels[$channel][] = [
            'message' => $message,
            'type' => $type
        ];

        session()->flash($channel, self::$channels[$channel]);
    }

    public static function allFromChannel($channel) {
        return session()->get($channel, []);
    }

    public static function countFromChannel($channel) {
        return count(session()->get($channel, []));
    }

    public const DANGER = 'danger';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const PRIMARY = 'primary';
    public const SECONDARY = 'secondary';
    public const LIGHT = 'light';
    public const DARK = 'dark';
}