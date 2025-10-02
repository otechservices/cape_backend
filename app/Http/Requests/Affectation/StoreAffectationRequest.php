<?php

namespace App\Http\Requests\Affectation;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isLast'      => 'required|boolean',
            'instruction' => 'required|string',
            'delay'       => 'required|date',
            'user_up'     => 'required|integer|exists:users,id',
            'user_down'   => 'required|integer|exists:users,id',
            'requete_id'  => 'required|integer|exists:requetes,id',
            'sens'        => 'sometimes|integer',        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages(): array
    {
        return [
        'isLast.required'        => 'Le champ isLast doit être un booléen.',
        'instruction.required'    => 'L\'instruction doit être une chaîne de caractères.',
        'delay.date'            => 'Le délai doit être une date valide.',
        'user_up.required'      => 'L\'ID de l\'utilisateur en amont est requis.',        
        'user_down.required'    => 'L\'ID de l\'utilisateur en aval est requis.',
        'requete_id.required'    => 'L\'ID de la requête doit être un entier.',        
        'sens.required'          => 'Le champ sens doit être un entier.',       
        ];
    }

    protected function prepareForValidation() {}
}
