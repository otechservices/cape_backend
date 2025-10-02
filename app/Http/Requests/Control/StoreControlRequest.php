<?php

namespace App\Http\Requests\Control;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreControlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observation'     => 'required|string',
            'date_control'    => 'required|date',
            'cape_id'         => 'required|integer|exists:capes,id',
            'type_control_id' => 'required|integer|exists:type_controls,id',
            'user_id'         => 'required|integer|exists:users,id',
            'is_valid'        => 'required|boolean',
            'chief_name'      => 'required|string|max:191',
            'members'         => 'required|string',
                ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'observation.required'     => 'L\'observation est requise.',
            'date_control.required'    => 'La date du contrôle est requise.',
            'cape_id.required'         => 'L\'ID du CAPE est requis.',
            'type_control_id.required' => 'L\'ID du type de contrôle est requis.',
            'user_id.required'         => 'L\'ID de l\'utilisateur est requis.',
            'chief_name.required'      => 'Le nom du chef est requis.',
            'members.required'         => 'La liste des membres est requise.',
            'is_valid.required'         => 'Le statut de validation doit être vrai ou faux.',                ];
    }

    protected function prepareForValidation() {}
}
