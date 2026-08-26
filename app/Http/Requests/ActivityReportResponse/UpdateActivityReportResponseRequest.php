<?php

namespace App\Http\Requests\ActivityReportResponse;

use App\Http\Requests\BaseFormRequest;

class UpdateActivityReportResponseRequest extends BaseFormRequest
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
            'instruction' => 'required|string',
            'activity_report_id' => 'required|integer',   
            ];
    }
}
