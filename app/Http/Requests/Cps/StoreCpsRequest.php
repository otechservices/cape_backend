<?php

namespace App\Http\Requests\Cps;

use App\Http\Requests\BaseFormRequest;

class StoreCpsRequest extends BaseFormRequest
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
            'name'            => 'required|string|max:191',
            'acronym'         => 'required|string|max:191',
            'name_chief'      => 'required|string|max:191',
            'phone'           => 'required|string|max:191',
            'email'           => 'required|email|max:191',
            'municipality_id' => 'required|integer|exists:municipalities,id',
                ];
    }
}
