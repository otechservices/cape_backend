<?php

namespace App\Http\Requests\UserAuth;

use App\Http\Requests\BaseFormRequest;

class GetUserPermissionRequest extends BaseFormRequest
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
            'project_id' => 'required',
            'label_names' => 'required|array',
        ];
    }
}
