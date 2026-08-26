<?php

namespace App\Http\Requests\Cps;

use App\Http\Requests\BaseFormRequest;

class UpdateCpsRequest extends BaseFormRequest
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
            'code' => 'sometimes|required|string|max:50',
            'identite' => 'sometimes|required|string|max:255',
        ];
    }
}
