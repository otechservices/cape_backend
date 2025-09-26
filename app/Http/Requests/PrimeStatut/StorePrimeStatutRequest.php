<?php

namespace App\Http\Requests\Corps;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCorpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut_id' => 'required|string|max:50|unique:code',
            'prime_id' => 'required|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'statut_id.required' => 'id statut est requis.',
            'prime_id.required' => 'id prime est requis.',
                ];
    }

    protected function prepareForValidation() {}
}
