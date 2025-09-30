<?php

namespace App\Http\Requests\Cps;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:191',
            'acronym'         => 'required|string|max:191',
            'name_chief'      => 'required|string|max:191',
            'phone'           => 'required|string|max:191',
            'email'           => 'required|email|max:191',
            'municipality_id' => 'required|integer|exists:municipalities,id',
                ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Le nom est requis.',
            'acronym.required'         => 'L\'acronyme est requis.',
            'acronym.required'           => 'L\'acronyme doit être une chaîne de caractères.',
            'name_chief.required'      => 'Le nom du chef est requis.',
            'name_chief.required'        => 'Le nom du chef doit être une chaîne de caractères.',
            'phone.required'           => 'Le numéro de téléphone est requis.',
            'email.required'           => 'L\'email est requis.',
            'municipality_id.required'  => 'L\'ID de la commune doit être un entier.',
                ];
    }

    protected function prepareForValidation() {}
}
