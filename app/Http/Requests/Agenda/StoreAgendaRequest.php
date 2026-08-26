<?php

namespace App\Http\Requests\Agenda;

use App\Http\Requests\BaseFormRequest;

class StoreAgendaRequest extends BaseFormRequest
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
            'invite_date' => 'required|date',
            'status'      => 'required|integer',
            'requete_id'  => 'nullable|integer|exists:requetes,id',
            'cps_id'      => 'nullable|integer|exists:cps,id',
        ];
    }
}
