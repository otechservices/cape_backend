<?php

namespace App\Http\Requests\ActivityReport;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateActivityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'centre_id' => 'nullable|exists:requetes,id',
            'description' => 'nullable|string',
            'activity_report_filename' => 'required|string',
            'financial_report_filename' => 'required|string',
            'status' => 'nullable|integer',
            'cape_id' => 'required|integer',
            'is_transmitted' => 'nullable|boolean',
   ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'activity_report_filename.required' => 'Le fichier du rapport d\'activité est requis.',
            'financial_report_filename.required' => 'Le fichier du rapport financier est requis.',
            'status.integer' => 'Le statut doit être un entier.',
            'status.in' => 'Le statut doit être requis.',
            'cape_id.required' => 'L\'ID du CAPE est requis.',
            'is_transmitted.boolean' => 'Le champ is_transmitted doit être vrai ou faux.',       ];
    }

    protected function prepareForValidation() {}
}
