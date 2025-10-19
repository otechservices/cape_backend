<?php

namespace App\Http\Requests\Staff;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreStaffRequest extends FormRequest
{   /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        // Tu peux adapter selon tes règles d'autorisation
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [
            'firstname'   => ['required', 'string', 'max:100'],
            'lastname'    => ['required', 'string', 'max:100'],
            'birthdate'   => ['required', 'date', 'before:today'],
            'birthplace'  => ['required', 'string', 'max:150'],
            'address'     => ['required', 'string', 'max:255'],
            'phone'       => ['required', 'regex:/^[0-9]{8,15}$/'],
            'email'       => ['required', 'email', 'max:150', 'unique:staff,email'],
            'job'         => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Messages d’erreur personnalisés (en français).
     */
    public function messages(): array
    {
        return [
            'firstname.required'  => 'Le prénom est obligatoire.',
            'lastname.required'   => 'Le nom de famille est obligatoire.',
            'birthdate.required'  => 'La date de naissance est obligatoire.',
            'birthdate.date'      => 'La date de naissance doit être une date valide.',
            'birthdate.before'    => 'La date de naissance doit être antérieure à aujourd’hui.',
            'birthplace.required' => 'Le lieu de naissance est obligatoire.',
            'address.required'    => 'L’adresse est obligatoire.',
            'phone.required'      => 'Le numéro de téléphone est obligatoire.',
            'phone.regex'         => 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.',
            'email.required'      => 'L’adresse e-mail est obligatoire.',
            'email.email'         => 'L’adresse e-mail doit être valide.',
            'email.unique'        => 'Cette adresse e-mail est déjà utilisée.',
            'job.string'          => 'Le métier doit être une chaîne de caractères valide.',
        ];
    }
    protected function prepareForValidation() {}
}
