<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseFormRequest;

class UpdateBillingRequest extends BaseFormRequest
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
           'object' => 'required|string',
            'content' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'type_billing_id' => 'required|integer',
            'priorite' => 'required|in:Urgente,Normale,Faible',

        ];
    }
}
