<?php

namespace App\Http\Requests\Typeacte;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTypeacteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|string|max:50',
            'libelle' => 'sometimes|required|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le nom est requis.',
            'libelle.required' => 'Le lieu est requis.',
        ];
    }

    protected function prepareForValidation() {}
}
