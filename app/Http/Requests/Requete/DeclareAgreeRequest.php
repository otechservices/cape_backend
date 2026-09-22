<?php

namespace App\Http\Requests\Requete;

use App\Http\Requests\BaseFormRequest;

/**
 * Déclaration d'un centre déjà agréé : mêmes exigences que la validation d'un
 * agrément (référence et scan de l'arrêté), le dossier étant validé d'emblée.
 */
class DeclareAgreeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'                   => ['required', 'exists:requetes,id'],
            'aggreement_reference' => ['required', 'string', 'max:255'],
            'aggreement_year'      => ['nullable', 'digits:4'],
            'file_aggreement'      => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'observation'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}
