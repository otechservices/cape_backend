<?php

namespace App\Http\Requests\BillingFile;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBillingFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filename'   => 'required|string',
            'billing_id' => 'required|integer|exists:billings,id',   
             ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'filename.required'   => 'Le nom du fichier est requis.',
            'billing_id.required' => 'L\'ID de la facturation est requis.',
        ];
    }

    protected function prepareForValidation() {}
}
