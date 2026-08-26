<?php

namespace App\Http\Requests\UserSetting;

use App\Http\Requests\BaseFormRequest;

class UpdateUserSettingRequest extends BaseFormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'use_2FA' => 'nullable|boolean',
            'accept_notification' => 'nullable|boolean',
            'notification_list' => 'nullable|array',
            'notification_list.*' => 'string|max:255',
            'mode_2FA' => 'nullable|string|in:SMS,EMAIL,WHATSAPP',
        ];
    }
}
