<?php

namespace App\Http\Requests\BillingFile;

use App\Http\Requests\BaseFormRequest;

class StoreBillingFileRequest extends BaseFormRequest
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
            'filename'   => 'required|string',
            'billing_id' => 'required|integer|exists:billings,id',        ];
    }
}
