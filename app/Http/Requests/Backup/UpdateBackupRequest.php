<?php

namespace App\Http\Requests\Backup;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:191',
            'filename' => 'nullable|string|max:191',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du document est requis.',
            'filename.required' => 'Le nom du fichier est requis.',            
        ];
    }

    protected function prepareForValidation() {}
}
