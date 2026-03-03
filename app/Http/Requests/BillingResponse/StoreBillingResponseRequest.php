<?php

namespace App\Http\Requests\BillingResponse;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBillingResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'    => 'required|string',
            'sens'=>'required|in:in,out',
            'billing_id' => 'required|integer|exists:billings,id',        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'content.required'    => 'Le contenu est requis.',
            'billing_id.required' => 'L\'ID de la facturation est requis.',
                ];
    }

    protected function prepareForValidation() {}
}
