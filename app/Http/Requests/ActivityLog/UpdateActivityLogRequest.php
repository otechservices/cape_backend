<?php

namespace App\Http\Requests\ActivityLog;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'log_name'      => 'required|string|max:191',
            'description'   => 'required|required|string',
            'subject_type'  => 'required|string|max:191',
            'event'         => 'required|string|max:191',
            'subject_id'    => 'required|integer',
            'causer_type'   => 'required|string|max:191',
            'causer_id'     => 'required|integer',
            'properties'    => 'required|string',
            'batch_uuid'    => 'required|string|size:36',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'log_name.required'      => 'Le nom du log est requis.',
            'description.required'   => 'La description est requise.',
            'subject_type.required'  => 'Le type du sujet est requis.',
            'event.required'         => 'L\'événement est requis.',
            'subject_id.required'    => 'L\'identifiant du sujet est requis.',
            'causer_type.required'   => 'Le type de l\'auteur est requis.',
            'causer_id.required'     => 'L\'identifiant de l\'auteur est requis.',
            'properties.required'    => 'Les propriétés sont requises.',
            'batch_uuid.required'    => 'Le batch_uuid est requis.',
        ];
    }

    protected function prepareForValidation() {}
}
