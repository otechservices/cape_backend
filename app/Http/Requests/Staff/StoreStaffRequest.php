<?php

namespace App\Http\Requests\Staff;

use App\Http\Requests\BaseFormRequest;
use App\Rules\CentreAgree;

class StoreStaffRequest extends BaseFormRequest
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
            'centre_id'   => ['required', 'exists:requetes,id', new CentreAgree],
            'firstname'   => ['required', 'string', 'max:100'],
            'lastname'    => ['required', 'string', 'max:100'],
            'birthdate'   => ['required', 'date', 'before:today'],
            'birthplace'  => ['required', 'string', 'max:150'],
            'address'     => ['required', 'string', 'max:255'],
            'phone'       => ['required', 'regex:/^[0-9]{8,15}$/'],
            'email'       => ['required', 'email', 'max:150', 'unique:staff,email'],
            'job'         => ['nullable', 'string', 'max:150'],
        ];
    }
}
