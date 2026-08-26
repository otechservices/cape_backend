<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseFormRequest;

class UpdatePermissionRequest extends BaseFormRequest
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
        $permissionId = $this->route('permission') instanceof \App\Models\Permission
        ? $this->route('permission')->id
        : $this->route('permission');
        //  'name' => 'sometimes|string|max:255|unique:permissions,name,' . $permissionId . ',id',

        return [
            'name' => 'sometimes|string|max:255',
            'guard_name' => 'required|string|max:255',
            'show_edit' => 'required|boolean',
            'show_only' => 'required|boolean',
        ];
    }
}
