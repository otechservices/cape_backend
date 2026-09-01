<?php

namespace App\Http\Requests\Resident;

use App\Http\Requests\BaseFormRequest;
use App\Rules\CentreAgree;

class StoreResidentRequest extends BaseFormRequest
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
            'centre_id' => ['required', 'exists:requetes,id', new CentreAgree],
            'firstname'   => ['required', 'string', 'max:100'],
            'lastname'    => ['required', 'string', 'max:100'],
            'birthdate'   => ['required', 'date', 'before:today'],
            'birthplace'  => ['required', 'string', 'max:150'],
            'address'     => ['required', 'string', 'max:255'],
            'sex'         => ['required', 'in:Masculin,Féminin'],
            'size'        => ['nullable', 'numeric', 'min:0'],
            'weight'      => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
