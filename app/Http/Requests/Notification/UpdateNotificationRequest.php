<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\BaseFormRequest;

class UpdateNotificationRequest extends BaseFormRequest
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
            'type' => 'required|string',
            'notifiable_type' => 'required|string',
            'notifiable_id' => 'required|integer',
            'data' => 'required|string',
            'read_at' => 'nullable|date',
        ];
    }
}
