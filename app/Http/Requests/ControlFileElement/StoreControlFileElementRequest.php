<?php

namespace App\Http\Requests\ControlFileElement;

use App\Http\Requests\BaseFormRequest;

class StoreControlFileElementRequest extends BaseFormRequest
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
            'name'       => 'required|string|max:191',
            'note_max'   => 'required|numeric',
            'is_active'  => 'sometimes|boolean',
            'service_id' => 'required|integer|exists:services,id',        ];
    }
}
