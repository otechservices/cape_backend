<?php

namespace App\Http\Requests\UserAuth;

use App\Http\Requests\BaseFormRequest;

class UpdateProfilRequest extends BaseFormRequest
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
            'email' => 'required|email|unique:users,id,email',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'phone' => 'required|string|max:20'

        ];
    }
}
