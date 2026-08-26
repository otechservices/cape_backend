<?php

namespace App\Http\Requests\District;

use App\Http\Requests\BaseFormRequest;

class StoreDistrictRequest extends BaseFormRequest
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
            'municipality_id' => 'required|integer|exists:municipalities,id',
            'cps_id'          => 'required|integer|exists:cps,id',
            'is_active'       => 'sometimes|boolean',        ];
    }
}
