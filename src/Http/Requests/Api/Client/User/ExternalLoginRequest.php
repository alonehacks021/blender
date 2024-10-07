<?php

namespace Nhd\Foundation\Requests\Api\Client\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Nhd\Foundation\Support\Alert;
use Illuminate\Validation\Rule;

class ExternalLoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'national_code' => 'required|string|max:50',
        ];
    }
}
