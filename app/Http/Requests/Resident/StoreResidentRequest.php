<?php
namespace App\Http\Requests\Resident;

use Illuminate\Foundation\Http\FormRequest;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname'   => ['required', 'string', 'max:100'],
            'lastname'    => ['required', 'string', 'max:100'],
            'birthdate'   => ['required', 'date', 'before:today'],
            'birthplace'  => ['required', 'string', 'max:150'],
            'address'     => ['required', 'string', 'max:255'],
            'sex'         => ['required', 'in:Masculin,Féminin'],
            'size'        => ['nullable', 'numeric', 'min:0'],
            'weight'      => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required'   => 'Le prénom est obligatoire.',
            'lastname.required'    => 'Le nom de famille est obligatoire.',
            'birthdate.required'   => 'La date de naissance est obligatoire.',
            'birthdate.date'       => 'La date de naissance doit être une date valide.',
            'birthdate.before'     => 'La date de naissance doit être antérieure à aujourd’hui.',
            'birthplace.required'  => 'Le lieu de naissance est obligatoire.',
            'address.required'     => 'L’adresse est obligatoire.',
            'sex.required'         => 'Le sexe est obligatoire.',
            'sex.in'               => 'Le sexe doit être Masculin ou Féminin.',
            'size.numeric'         => 'La taille doit être un nombre.',
            'size.min'             => 'La taille doit être supérieure ou égale à 0.',
            'weight.numeric'       => 'Le poids doit être un nombre.',
            'weight.min'           => 'Le poids doit être supérieur ou égal à 0.',
        ];
    }
}
