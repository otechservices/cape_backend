<?php

namespace App\Http\Requests\Service;

use App\Http\Requests\BaseFormRequest;

class StoreServiceRequest extends BaseFormRequest
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
            'title' => 'required|string|max:191',
            'subtitle' => 'required|string|max:191',
            'big_photo' => 'required|string',
            'short_photo' => 'required|string',
            'resume' => 'required|string',
            'content' => 'required|string',
            'author' => 'required|string|max:191',
            'is_active' => 'required|boolean',
            'user_id' => 'required|integer',        ];
    }
}
