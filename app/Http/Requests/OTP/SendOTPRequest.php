<?php

namespace App\Http\Requests\OTP;

use App\Http\Requests\BaseFormRequest;

class SendOTPRequest extends BaseFormRequest
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
            'phone' => 'required|string', // prevoir une regex
            'email' => 'required|email|string',
            'canal' => 'required|array|in:SMS,EMAIL,APP',
        ];
    }
}
