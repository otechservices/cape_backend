<?php

namespace App\Http\Requests\ActivityReport;

use App\Http\Requests\BaseFormRequest;
use App\Rules\CentreAgree;

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
            'centre_id' => ['required', 'exists:requetes,id', new CentreAgree],
            'description' => 'nullable|string',
            'activity_report_filename' => 'required',
            'financial_report_filename' => 'required',
            'status' => 'nullable|integer',
            'is_transmitted' => 'nullable|boolean',        ];
    }
}
