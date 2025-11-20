<?php

namespace App\Imports;

use App\Models\Requete;
use App\Models\District;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GarderiesImport implements ToCollection, WithHeadingRow
{
    protected $defaultPromoterId;

    public function __construct($defaultPromoterId = 1)
    {
        $this->defaultPromoterId = $defaultPromoterId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // --- Récupération tolérante des colonnes selon tes en-têtes ---
            $numero = data_get($row, 'n°') ?? data_get($row, 'n') ?? data_get($row, 'numero') ?? null;
            $departement = data_get($row, 'départements') ?? data_get($row, 'departement') ?? data_get($row, 'departements') ?? null;
            $commune = data_get($row, 'commune') ?? data_get($row, 'communes') ?? null;
            $arrondissement = data_get($row, 'arrondissement') ?? data_get($row, 'arrondisse-ment') ?? data_get($row, 'arrondissment') ?? null;
            $denomination = data_get($row, 'dénomination') ?? data_get($row, 'denomination') ?? data_get($row, 'dénomina') ?? null;
            $adresse = data_get($row, 'adresse') ?? data_get($row, 'address') ?? null;
            // éventuellement d'autres colonnes si présentes
            $contacts = data_get($row, 'contacts') ?? data_get($row, 'contact') ?? null;

            // extraction contact simple (si fourni dans une colonne contacts)
            $email = null;
            $phone = null;
            if ($contacts) {
                $parts = preg_split('/[;,\/\n]+/', $contacts);
                foreach ($parts as $p) {
                    $p = trim($p);
                    if (!$p) continue;
                    if (strpos($p, '@') !== false) {
                        $email = $email ?? $p;
                    } else {
                        $candidate = preg_replace('/[^\d\+]/', '', $p);
                        if (strlen($candidate) >= 7) {
                            $phone = $phone ?? $candidate;
                        } else {
                            $phone = $phone ?? $p;
                        }
                    }
                }
            }

            // Résolution / création du district (priorité Arrondissement > Commune > Département)
            $district_name = $arrondissement ?: $commune ?: $departement;
            $district_id = null;
            if ($district_name) {
                $district = District::firstOrCreate(
                    ['name' => trim($district_name)],
                    ['is_active' => 1]
                );
                $district_id = $district->id;
            }

                $requete = Requete::where('name', $denomination)->first();

            // Préparer les données
            $data = [
                'code' => $code ?? 'GARD-'.Str::upper(Str::random(6)),
                'name' => $denomination ?? 'N/A',
                'address' => $adresse,
                'email' => $email,
                'phone' => $phone,
                'type_cape_id' => 2, // <--- garderie
                'district_id' => $district_id,
                'status' => 9,
                'promoter_id' => $this->defaultPromoterId,
                // info complémentaires
                'town' => $commune,
                'observation' => $departement ? "Département: $departement" : null,
            ];

            // Nettoyage : retirer clés nulles (si tu préfères enregistrer NULL explicitement, supprime la ligne suivante)
            $data = array_filter($data, function ($v) { return !($v === null && $v !== 0); });

            // Update or create
            if ($requete) {
                info('ici');
                $requete->update($data);
            } else {

                if (!empty($denomination)) {
                                    info('ici2');

                Requete::create($data);
                }
            }
        }
    }
}
