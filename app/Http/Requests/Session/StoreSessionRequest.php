<?php

namespace App\Http\Requests\Session;

use App\Http\Requests\BaseFormRequest;

class StoreSessionRequest extends BaseFormRequest
{
    /**
     * Création d'une session : libellé, période et note d'accord (PDF, affichée
     * dans le visualiseur de l'écran des sessions).
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'file' => 'required|file|mimes:pdf|max:20480',
        ];
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'name' => 'libellé',
            'date_start' => 'date de début',
            'date_end' => 'date de fin',
            'file' => "note d'accord",
        ]);
    }
}
