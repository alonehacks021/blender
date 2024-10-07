<?php

namespace Nhd\Foundation\Http\Requests\Dashboard\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Nhd\Foundation\Support\Alert;
use Illuminate\Validation\Rule;

class RolesRequest extends FormRequest
{
    public function rules()
    {
        return [
            'roles' => 'nullable|array|exists:roles,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Alert::add('بروزرسانی نقش های کاربر انجام نشد', Alert::DANGER);
        parent::failedValidation($validator);
    }
}
