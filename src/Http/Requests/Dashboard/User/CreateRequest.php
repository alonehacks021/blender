<?php

namespace Nhd\Foundation\Http\Requests\Dashboard\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Nhd\Foundation\Support\Alert;
use Illuminate\Validation\Rule;
use Nhd\Foundation\Models\User;

class CreateRequest extends FormRequest
{
    public function rules()
    {
        return [
            '*' => 'bail',
            'username' => 'required|alpha_dash|max:255|unique:users,username',
            'password' => ['nullable', 'string', 'min:8', 'max:16', Rule::requiredIf(function() {
                return $this->get('type') == User::TYPE_ADMIN;
            })],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'mobile' => 'required|starts_with:09|digits:11|unique:users,mobile',
            'image' => 'nullable|image|max:2048',
            'gender' => ['required', Rule::in(User::getGenders())],
            'status' => ['required', Rule::in(User::getStatuses())],
            'type' => ['nullable', Rule::in(User::getTypes())],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Alert::add('افزودن کاربر انجام نشد', Alert::DANGER);
        parent::failedValidation($validator);
    }
}
