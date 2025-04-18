<?php

namespace App\Http\Requests\Periode;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePeriodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:code',
            'libelle' => 'required|string|max:255',
            'periode' => 'required|date',
            'type_periode' => 'required|string|in:Periode1,Periode1',
       ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est requis.',
            'libelle.required' => 'Le libellé est requis.',
            'libelle.periode' => 'Le libellé est requis.',
            'libelle.type_periode' => 'Le libellé est requis.',
                ];
    }

    protected function prepareForValidation() {}
}
