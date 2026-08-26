<?php

namespace App\Http\Requests\Resident;

use App\Http\Requests\BaseFormRequest;

class UpdateResidentRequest extends BaseFormRequest
{
    /**
     * Règles de validation appliquées à la requête.
     *
     * Les messages d'erreur français sont fournis automatiquement par
     * lang/fr/validation.php (règles) et lang/fr/attributes.php (libellés
     * des champs), via BaseFormRequest.
     */
    public function rules(): array
    {
        return [
            'centre_id' => 'nullable|exists:requetes,id',
            'firstname'   => ['sometimes', 'required', 'string', 'max:100'],
            'lastname'    => ['sometimes', 'required', 'string', 'max:100'],
            'birthdate'   => ['sometimes', 'required', 'date', 'before:today'],
            'birthplace'  => ['sometimes', 'required', 'string', 'max:150'],
            'address'     => ['sometimes', 'required', 'string', 'max:255'],
            'sex'         => ['sometimes', 'required', 'in:Masculin,Féminin'],
            'size'        => ['nullable', 'numeric', 'min:0'],
            'weight'      => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
