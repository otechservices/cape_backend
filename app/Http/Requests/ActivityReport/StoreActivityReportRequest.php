<?php

namespace App\Http\Requests\ActivityReport;

use App\Http\Requests\BaseFormRequest;

class StoreActivityReportRequest extends BaseFormRequest
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
            'centre_id' => 'nullable|exists:requetes,id',
            'description' => 'nullable|string',
            'activity_report_filename' => 'required',
            'financial_report_filename' => 'required',
            'status' => 'nullable|integer',
            'is_transmitted' => 'nullable|boolean',        ];
    }
}
