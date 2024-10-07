<?php

namespace Nhd\Foundation\Http\Requests\Dashboard\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Nhd\Foundation\Support\Alert;

class DefaultsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'two_step_login_dashboard' => 'boolean',
            'two_step_login_client' => 'boolean',
            'two_step_login_sms' => 'nullable|string|max:133',

            'is_active_ldap' => 'nullable|boolean',
            'organizational_post_roles' => 'nullable|array',
            'organizational_post_roles.*' => 'nullable|array|exists:roles,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Alert::add('تنظیمات اعمال نگردید', Alert::DANGER);
        parent::failedValidation($validator);
    }
}
