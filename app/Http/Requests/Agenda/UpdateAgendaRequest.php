<?php

namespace App\Http\Requests\Agenda;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invite_date' => 'required|date',
            'status'      => 'required|integer',
            'requete_id'  => 'nullable|integer|exists:requetes,id',
            'cps_id'      => 'nullable|integer|exists:cps,id',        
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'invite_date.required' => 'La date de l\'invitation est requise.',
            'invite_date.date'     => 'La date de l\'invitation doit être une date valide.',
            'status.required'      => 'Le statut est requis.',
            'status.integer'       => 'Le statut doit être un entier.',
            'requete_id.integer'   => 'L\'ID de la requête doit être un entier.',
            'cps_id.integer'       => 'L\'ID du CPS doit être un entier.',
            'cps_id.required'        => 'Le CPS spécifié est requis.',       
        ];
    }

    protected function prepareForValidation() {}
}
