<?php

namespace App\Http\Requests\ActivityReportResponse;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateActivityReportResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'instruction' => 'required|string',
            'activity_report_id' => 'required|integer',   
            ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'instruction.required' => 'Le champ instruction est requis.',
            'activity_report_id.required' => 'L\'ID du rapport d\'activité est requis.',
        ];
    }

    protected function prepareForValidation() {}
}
