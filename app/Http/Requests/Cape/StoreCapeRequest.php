<?php

namespace App\Http\Requests\Cape;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCapeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'     => 'required|integer',
            'requete_id' => 'required|integer|exists:requetes,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'status.required'     => 'Le statut est requis.',
            'requete_id.required' => 'L\'ID de la requête est requis.',
                ];
    }

    protected function prepareForValidation() {}
}
