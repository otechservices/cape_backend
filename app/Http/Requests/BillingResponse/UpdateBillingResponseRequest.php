<?php

namespace App\Http\Requests\BillingResponse;

use App\Http\Requests\BaseFormRequest;

class UpdateBillingResponseRequest extends BaseFormRequest
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
            'content'    => 'required|string',
             'sens'=>'required|in:in,out',
            'billing_id' => 'required|integer|exists:billings,id',    
            ];
    }
}
