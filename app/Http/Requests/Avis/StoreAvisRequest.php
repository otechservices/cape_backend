<?php

namespace App\Http\Requests\Avis;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAvisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observation'        => 'required|string',
            'treatment'          => 'required|string',
            'decision'           => 'nullable|boolean',
            'session_member_id'  => 'required|integer|exists:session_members,id',
            'requete_id'         => 'required|integer|exists:requetes,id',
            'note'               => 'required|integer',        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'observation.required'        => 'L\'observation est requise.',
            'treatment.required'          => 'Le traitement est requis.',
            'decision.boolean'            => 'La décision doit être un booléen.',
            'session_member_id.required'  => 'L\'ID du membre de session est requis.',
            'requete_id.required'         => 'L\'ID de la requête est requis.',
            'note.required'               => 'La note est requise.',
                ];
    }

    protected function prepareForValidation() {}
}
