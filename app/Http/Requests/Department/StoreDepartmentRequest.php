<?php

namespace App\Http\Requests\Department;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:191',
            'is_active' => 'sometimes|boolean', 
            ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Le nom est requis.',
            'is_active.required'  => 'Le statut doit être vrai ou faux.',                ];
    }

    protected function prepareForValidation() {}
}
