<?php

namespace App\Http\Requests\Control;

use App\Http\Requests\BaseFormRequest;

class StoreControlRequest extends BaseFormRequest
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
            'observation'     => 'required|string',
            'date_control'    => 'required|date',
            'cape_id'         => 'required|integer|exists:capes,id',
            'type_control_id' => 'required|integer|exists:type_controls,id',
            'user_id'         => 'required|integer|exists:users,id',
            'is_valid'        => 'required|boolean',
            'chief_name'      => 'required|string|max:191',
            'members'         => 'required|string',
                ];
    }
}
