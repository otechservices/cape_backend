<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class UpdateUserRequest extends BaseFormRequest
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
              'email' => 'required|email|unique:users,email',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'role' => 'required',
        ];
    }
}
