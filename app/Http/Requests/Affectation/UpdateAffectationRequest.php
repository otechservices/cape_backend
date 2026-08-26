<?php

namespace App\Http\Requests\Affectation;

use App\Http\Requests\BaseFormRequest;

class UpdateAffectationRequest extends BaseFormRequest
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
            'isLast'      => 'required|boolean',
            'instruction' => 'required|string',
            'delay'       => 'required|date',
            'user_up'     => 'required|integer|exists:users,id',
            'user_down'   => 'required|integer|exists:users,id',
            'requete_id'  => 'required|integer|exists:requetes,id',
            'sens'        => 'sometimes|integer',        
        ];
    }
}
