<?php

namespace App\Http\Requests\Cape;

use App\Http\Requests\BaseFormRequest;

class UpdateCapeRequest extends BaseFormRequest
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
            'status'     => 'required|integer',
            'requete_id' => 'required|integer|exists:requetes,id',
        ];
    }
}
