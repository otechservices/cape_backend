<?php

namespace App\Http\Requests\Billing;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'object' => 'required|string',
            'content' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'type_billing_id' => 'required|integer',
            'priorite' => 'required|in:Urgente,Normale,Faible',
            ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'object.required' => 'L\'objet est requis.',
            'name.required' => 'Le nom est requis.',
            'email.required' => 'L\'email est requis.',
            'content.required' => 'Le contenu est requis.',
            'token.required' => 'Le token est requis.',
            'is_open.required' => 'Le champ is_open est requis.',
            'type_billing_id.required' => 'L\'ID du type de facturation est requis.',
        ];    
    }

    protected function prepareForValidation() {}
}
