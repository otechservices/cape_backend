<?php

namespace App\Http\Requests\ControlFileElement;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreControlFileElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:191',
            'note_max'   => 'required|numeric',
            'is_active'  => 'sometimes|boolean',
            'service_id' => 'required|integer|exists:services,id',        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Le nom est requis.',
            'note_max.required'   => 'La note maximale est requise.',
            'is_active.required'   => 'Le statut doit être vrai ou faux.',
            'service_id.required' => 'L\'ID du service est requis.',
                    ];
    }

    protected function prepareForValidation() {}
}
