<?php

namespace App\Http\Requests\ActivityLog;

use App\Http\Requests\BaseFormRequest;

class StoreActivityLogRequest extends BaseFormRequest
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
            'log_name'      => 'required|string|max:191',
            'description'   => 'required|required|string',
            'subject_type'  => 'required|string|max:191',
            'event'         => 'required|string|max:191',
            'subject_id'    => 'required|integer',
            'causer_type'   => 'required|string|max:191',
            'causer_id'     => 'required|integer',
            'properties'    => 'required|string',
            'batch_uuid'    => 'required|string|size:36',
        ];
    }
}
