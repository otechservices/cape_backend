<?php

namespace App\Http\Requests\BillingResponse;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBillingResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'    => 'required|string',
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
            'content.required' => 'Le nom est requis.',
            'billing_id.required' => 'Le lieu est requis.',
        ];
    }

    protected function prepareForValidation() {}
}
