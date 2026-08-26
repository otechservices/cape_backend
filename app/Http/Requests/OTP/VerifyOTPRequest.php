<?php

namespace App\Http\Requests\OTP;

use App\Http\Requests\BaseFormRequest;

class VerifyOTPRequest extends BaseFormRequest
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
            'phone' => 'nullable|string', // prevoir une regex
            'email' => 'nullable|email|string',
            'author' => 'required_if:for_login,true|email|string',
            'for_login' => 'nullable|boolean',
            'verification_code' => 'required|integer',
        ];
    }
}
