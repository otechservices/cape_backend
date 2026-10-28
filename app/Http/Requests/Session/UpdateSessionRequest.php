<?php

namespace App\Http\Requests\Session;

use App\Http\Requests\BaseFormRequest;

class UpdateSessionRequest extends BaseFormRequest
{
    /**
     * Modification d'une session en préparation. La note d'accord n'est
     * remplacée que si un nouveau fichier est envoyé.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'file' => 'nullable|file|mimes:pdf|max:20480',
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
