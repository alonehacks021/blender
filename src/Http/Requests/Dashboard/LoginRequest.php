<?php

namespace Nhd\Foundation\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:2|max:16',
            'remember' => 'nullable|boolean',
            'captcha' => 'required|captcha',
        ];
    }
}
