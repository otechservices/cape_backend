<?php

namespace App\Http\Requests\Reponse;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'subtitle' => 'required|string|max:191',
            'big_photo' => 'required|string',
            'short_photo' => 'required|string',
            'resume' => 'required|string',
            'content' => 'required|string',
            'author' => 'required|string|max:191',
            'is_active' => 'required|boolean',
            'user_id' => 'required|integer',        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis.',
            'subtitle.string' => 'Le sous-titre doit être une chaîne de caractères.',
            'big_photo.required' => 'La big photo est requis.',
            'short_photo.required' => 'La short_photo est requis.',
            'resume.required' => 'Le résumé est requis.',
            'content.required' => 'Le contenu est requis.',
            'author.string' => 'Le nom de l\'auteur doit être une chaîne de caractères.',
            'user_id.required' => 'L\'ID de l\'utilisateur est requis.',
         ];
    }

    protected function prepareForValidation() {}
}
