<?php

namespace App\Http\Requests\Requete;

use App\Http\Requests\BaseFormRequest;

/**
 * Validation d'un agrément, pièce justificative à l'appui.
 *
 * La référence de l'arrêté et son scan sont exigés : c'est ce qui distingue
 * une décision motivée d'une simple case cochée. L'année reste facultative,
 * toutes les références n'en portant pas.
 */
class ValidateAgrementRequest extends BaseFormRequest
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
