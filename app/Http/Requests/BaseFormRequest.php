<?php

namespace App\Http\Requests;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Classe de base de toutes les FormRequests du projet.
 *
 * Elle centralise :
 *  - le format de réponse en cas d'échec de validation ({status, message, ...}) ;
 *  - la traduction française des messages, assurée par lang/fr/validation.php ;
 *  - la traduction des noms de champs, assurée par lang/fr/attributes.php.
 *
 * Une requête n'a donc plus qu'à déclarer ses rules(). Elle peut surcharger
 * messages() ou attributes() uniquement pour un libellé réellement spécifique.
 */
abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation. À implémenter dans chaque requête.
     */
    abstract public function rules(): array;

    /**
     * Messages spécifiques à la requête.
     *
     * Par défaut vide : les messages français génériques proviennent de
     * lang/fr/validation.php.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Libellés français des champs.
     *
     * Le dictionnaire commun est chargé depuis lang/fr/attributes.php ; toute
     * valeur retournée ici le complète ou le surcharge pour cette requête.
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Réponse renvoyée lorsque la validation échoue.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            Common::error($validator->errors()->first(), $validator->errors())
        );
    }

    /**
     * Préparation des données avant validation. Surchargeable si besoin.
     */
    protected function prepareForValidation(): void
    {
        //
    }
}
