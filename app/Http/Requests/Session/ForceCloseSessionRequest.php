<?php

namespace App\Http\Requests\Session;

use App\Http\Requests\BaseFormRequest;

class ForceCloseSessionRequest extends BaseFormRequest
{
    /**
     * Clôture forcée d'une session par la DFEA.
     *
     * `mode` : `continue` (les décisions prises poursuivent le workflow) ou
     * `cancel` (la session est annulée, ses dossiers sont à réinscrire).
     * `reason` : motif obligatoire, conservé sur la session et dans le parcours
     * de chaque dossier concerné.
     */
    public function rules(): array
    {
        return [
            'mode' => 'required|in:continue,cancel',
            'reason' => 'required|string|min:10|max:2000',
        ];
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'mode' => 'sort des dossiers',
            'reason' => 'motif',
        ]);
    }
}
