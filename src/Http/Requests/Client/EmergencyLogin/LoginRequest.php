<?php

namespace Nhd\Foundation\Http\Requests\Client\EmergencyLogin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Nhd\Foundation\Rules\NationalCodeRule;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => ['required', new NationalCodeRule()],
            'mobile' => 'required|numeric|starts_with:09|digits:11',
            'captcha' => 'required|captcha',
        ];
    }
}
