<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\BaseFormRequest;

class UpdateSettingRequest extends BaseFormRequest
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
            'key' => 'required|string|max:255',
            'value' => 'nullable|string|max:1000',
            'durer' => 'nullable|integer|min:1',
            'seance_interactive' => 'nullable|integer|min:1',
            'dure_deuiduite' => 'nullable|integer|min:0',
        ];
    }
}
