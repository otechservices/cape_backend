<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseFormRequest;

class UpdateRoleRequest extends BaseFormRequest
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
            'name' => 'sometimes|string|max:255|unique:roles,name,'.$this->route('role'), // Exclut le rôle actuel lors de la mise à jour
            // 'guard_name' => 'sometimes|string|max:255',
            'permissions' => 'nullable|array',
        ];
    }
}
