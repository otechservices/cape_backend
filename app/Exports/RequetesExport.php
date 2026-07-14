<?php

namespace App\Exports;

use App\Models\Requete;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel des dossiers CAPE / Garderies.
 *
 * Les filtres reçus sont ceux de l'écran de consultation : le fichier produit
 * correspond donc toujours à la liste que l'utilisateur a sous les yeux.
 */
class RequetesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    private int $line = 0;

    public function __construct(private array $filters = []) {}

    public function query()
    {
        return Requete::applyFilters($this->filters)
            ->with(['TypeCape', 'service', 'district.cps', 'district.Municipality.Department'])
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'N°',
            'Code dossier',
            'Dénomination',
            'Type',
            'Catégorie',
            'Agrément',
            'Statut du dossier',
            'Promoteur',
            'Email',
            'Téléphone',
            'Département',
            'Commune',
            'Arrondissement',
            'CPS de rattachement',
            "Capacité d'accueil",
            'Date de soumission',
            "Rapport d'enquête",
            'Enquêteur',
            "Date de l'enquête",
        ];
    }

    /**
     * @param  Requete  $requete
     */
    public function map($requete): array
    {
        $municipality = $requete->district?->Municipality;

        return [
            ++$this->line,
            $requete->code,
            $requete->name,
            $requete->service?->name,
            $requete->TypeCape?->name,
            $requete->is_agree ? 'Agréé' : 'Non agréé',
            self::statusLabel($requete->status),
            trim(($requete->name_pomoter ?? '').' '.($requete->firstname_pomoter ?? '')) ?: null,
            $requete->email,
            $requete->phone,
            $municipality?->Department?->name,
            $municipality?->name ?? $requete->town,
            $requete->district?->name,
            $requete->district?->cps?->name,
            $requete->capacity,
            $requete->created_at?->format('d/m/Y'),
            $requete->has_cps_file ? 'Oui' : 'Non',
            $requete->social_investigator_name,
            $requete->social_investigator_date
                ? Carbon::parse($requete->social_investigator_date)->format('d/m/Y')
                : null,
        ];
    }

    public function title(): string
    {
        return 'Dossiers';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Libellés du circuit de traitement (voir Requete::STATUS_*).
     */
    public static function statusLabel(?int $status): string
    {
        return [
            0 => 'Nouveau dossier',
            1 => 'Mise en attente',
            2 => 'Rejeté',
            3 => 'Dossier corrigé',
            4 => 'Invitation envoyée',
            5 => 'Transmis au DD',
            6 => 'Attente approbation DDASM',
            7 => 'Attente inscription session',
            8 => 'Agréé',
            9 => 'Agréé (avant plateforme)',
        ][$status] ?? 'Non défini';
    }
}
