<?php

namespace Nhd\Foundation\Rules;

use Illuminate\Contracts\Validation\Rule;

class PasswordRule implements Rule
{
    public function __construct()
    {
        //
    }

    public function passes($attribute, $value)
    {
        $phrase = mb_str_split($value);
        $numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $charsSmall = range('a', 'z');
        $charsBig = range('A', 'Z');
        $specials = ['@', '_'];

        if(count(array_intersect($phrase, $numbers)) == 0) {
            return false;
        }

        if(count(array_intersect($phrase, $charsSmall)) == 0) {
            return false;
        }

        if(count(array_intersect($phrase, $charsBig)) == 0) {
            return false;
        }

        if(count(array_intersect($phrase, $specials)) == 0) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return "رمزعبور می بایست شامل حداقل یک حرف بزرگ و یک حرف كوچک انگليسی و حداقل یک عدد و حداقل شامل یکی از کاراکتر های @ _ باشد";
    }
}
