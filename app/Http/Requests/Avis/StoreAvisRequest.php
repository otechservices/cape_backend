<?php

namespace App\Http\Requests\Avis;

use App\Http\Requests\BaseFormRequest;

class StoreAvisRequest extends BaseFormRequest
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
            'observation'        => 'required|string',
            'treatment'          => 'required|string',
            'decision'           => 'nullable|boolean',
            'session_member_id'  => 'required|integer|exists:session_members,id',
            'requete_id'         => 'required|integer|exists:requetes,id',
            'note'               => 'required|integer',        ];
    }
}
