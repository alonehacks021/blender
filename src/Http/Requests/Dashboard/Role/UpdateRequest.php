<?php

namespace Nhd\Foundation\Http\Requests\Dashboard\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Nhd\Foundation\Support\Alert;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:100|unique:roles,name,'.$this->role->id,
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Alert::add('بروزرسانی نقش انجام نشد', Alert::DANGER);
        parent::failedValidation($validator);
    }
}
