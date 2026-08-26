<?php

namespace App\Http\Requests\Staff;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends BaseFormRequest
{
    /**
     * Règles de validation appliquées à la requête.
     *
     * Les messages d'erreur français sont fournis automatiquement par
     * lang/fr/validation.php (règles) et lang/fr/attributes.php (libellés
     * des champs), via BaseFormRequest.
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
}
