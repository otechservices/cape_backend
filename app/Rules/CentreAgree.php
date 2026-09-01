<?php

namespace App\Rules;

use App\Models\Requete;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * N'autorise l'exploitation d'un centre qu'une fois son agrément reconnu.
 *
 * Personnel, pensionnaires et rapports d'activité décrivent le fonctionnement
 * d'un centre en activité : les saisir avant que la DFEA n'ait validé
 * l'agrément n'a pas de sens, et laisserait croire au promoteur que son dossier
 * avance. Le contrôle vit ici plutôt que dans l'écran, pour que l'interdiction
 * tienne aussi face à un appel direct à l'API.
 *
 * @see \App\Models\Requete::getIsAgreeAttribute()
 */
class CentreAgree implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return; // l'absence de centre est traitée par la règle « required »
        }

        $centre = Requete::find($value);

        if (! $centre || ! $centre->is_agree) {
            $fail("Ce centre n'est pas encore agréé. La DFEA doit d'abord valider son agrément avant toute saisie le concernant.");
        }
    }
}
