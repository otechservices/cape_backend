<?php

namespace App\Http\Requests\PromoterInvitation;

use App\Http\Requests\BaseFormRequest;

/**
 * Création d'un compte promoteur sur invitation. L'adresse email n'est pas
 * saisie : c'est celle à laquelle le lien a été envoyé.
 */
class AcceptPromoterInvitationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lastname' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:promoters,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
