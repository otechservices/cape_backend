<?php

namespace App\Http\Requests\District;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:191',
            'municipality_id' => 'required|integer|exists:municipalities,id',
            'cps_id'          => 'required|integer|exists:cps,id',
            'is_active'       => 'sometimes|boolean',        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Le nom est requis.',
            'municipality_id.required'  => 'L\'ID de la commune doit être un entier.',
            'cps_id.required'           => 'L\'ID du CPS doit être un entier.',
            'is_active.required'        => 'Le statut doit être vrai ou faux.',                ];
    }

    protected function prepareForValidation() {}
}
