<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return true; // Adapter selon tes règles d'autorisation
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        $staffId = $this->route('staff'); // récupère l'ID du staff depuis la route

        return [
            'firstname'   => ['required', 'string', 'max:100'],
            'lastname'    => ['required', 'string', 'max:100'],
            'birthdate'   => ['required', 'date', 'before:today'],
            'birthplace'  => ['required', 'string', 'max:150'],
            'address'     => ['required', 'string', 'max:255'],
            'phone'       => ['required', 'regex:/^[0-9]{8,15}$/'],
            'email'       => [
                'required',
                'email',
                'max:150',
                Rule::unique('staff', 'email')->ignore($staffId)
            ],
            'job'         => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Messages d’erreur personnalisés.
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
            'email.unique'        => 'Cette adresse e-mail est déjà utilisée par un autre membre du personnel.',
            'job.string'          => 'Le métier doit être une chaîne de caractères valide.',
        ];
    }
}
