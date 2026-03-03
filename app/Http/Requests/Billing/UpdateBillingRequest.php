<?php

namespace App\Http\Requests\Billing;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBillingRequest extends FormRequest
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
            'code.required' => 'Le nom est requis.',
            'identite.required' => 'Le lieu est requis.',
        ];
    }

    protected function prepareForValidation() {}
}
