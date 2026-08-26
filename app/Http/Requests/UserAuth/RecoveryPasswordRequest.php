<?php

namespace App\Http\Requests\UserAuth;

use App\Http\Requests\BaseFormRequest;

class RecoveryPasswordRequest extends BaseFormRequest
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
            'token' => 'required|string|exists:password_reset_tokens,token',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',

        ];
    }
}
