<?php

namespace App\Imports;

use App\Models\Requete;
use App\Models\District;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CapesImport implements ToCollection, WithHeadingRow
{
    protected $defaultPromoterId;

    public function __construct($defaultPromoterId = 1)
    {
        $this->defaultPromoterId = $defaultPromoterId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // --- Récupération robuste des colonnes d'après tes en-têtes ---
            $numero = data_get($row, 'n°') ?? data_get($row, 'n') ?? data_get($row, 'numero') ?? null;
            $departement = data_get($row, 'départements') ?? data_get($row, 'departements') ?? data_get($row, 'departement') ?? null;
            $commune = data_get($row, 'communes') ?? data_get($row, 'commune') ?? null;
            $arrondissement = data_get($row, 'arrondisse-ment') ?? data_get($row, 'arrondissement') ?? data_get($row, 'arrondissment') ?? null;
            $capeName = data_get($row, 'dénomination du cape') ?? data_get($row, 'denomination du cape') ?? data_get($row, 'denomination_du_cape') ?? data_get($row, 'dénomination_du_cape') ?? data_get($row, 'dénomination') ?? data_get($row, 'dénomination_du_cape') ?? data_get($row, 'dénominationducape') ?? null;
            $promoteurFull = data_get($row, 'nom et prénoms du promoteur') ?? data_get($row, 'nom et prénoms') ?? data_get($row, 'nom_et_prénoms_du_promoteur') ?? data_get($row, 'nom_prenoms_promoteur') ?? data_get($row, 'nom') ?? null;
            $cibles = data_get($row, 'cibles accueillies') ?? data_get($row, 'cibles') ?? null;
            $tranche_age = data_get($row, 'tranche d’âge accueillie') ?? data_get($row, 'tranche d age accueillie') ?? data_get($row, 'tranche_age') ?? null;
            $capacite = data_get($row, 'capacité d’accueil') ?? data_get($row, 'capacite d accueil') ?? data_get($row, 'capacite') ?? data_get($row, 'capacité') ?? null;
            $contacts = data_get($row, 'contacts') ?? data_get($row, 'contact') ?? null;

            // Extraire email/phone si possible depuis contacts
            $email = null;
            $phone = null;
            if ($contacts) {
                // si plusieurs valeurs séparées par ; , /
                $parts = preg_split('/[;,\/\n]+/', $contacts);
                foreach ($parts as $p) {
                    $p = trim($p);
                    if (!$p) continue;
                    if (strpos($p, '@') !== false) {
                        $email = $email ?? $p;
                    } else {
                        // garder chiffres + + ()
                        $candidate = preg_replace('/[^\d\+]/', '', $p);
                        if (strlen($candidate) >= 7) {
                            $phone = $phone ?? $candidate;
                        } else {
                            // si pas assez long on garde brut
                            $phone = $phone ?? $p;
                        }
                    }
                }
            }

            // Séparer nom/prénom du promoteur si possible
            $firstname_promoter = null;
            $name_promoter = null;
            if ($promoteurFull) {
                // coup simple : last token = prénom ou inverse ; on prend heuristique
                $parts = preg_split('/\s+/', trim($promoteurFull));
                if (count($parts) == 1) {
                    $name_promoter = $parts[0];
                } else {
                    $name_promoter = array_shift($parts);
                    $firstname_promoter = implode(' ', $parts);
                    // si heuristique pas bonne, on stocke full dans firstname_pomoter (compatibilité)
                }
            }

            // Trouver/Créer le district (on utilise Arrondissement si fourni, sinon Commune)
            $district_name = $arrondissement ?: $commune ?: $departement;
            $district_id = null;
            if ($district_name) {
                $district = District::firstOrCreate(
                    ['name' => trim($district_name)],
                    ['is_active' => 1]
                );
                $district_id = $district->id;
            }

            // Une ligne sans dénomination n'est pas exploitable : on l'ignore
            // plutôt que de créer un enregistrement fantôme nommé 'N/A'.
            $capeName = $capeName ? trim($capeName) : null;
            if (empty($capeName)) {
                continue;
            }

            // Rapprochement d'un enregistrement existant, restreint aux CAPE :
            // sans le filtre sur type_cape_id, une garderie homonyme serait écrasée.
            $requete = Requete::where('name', $capeName)
                ->where('type_cape_id', 1)
                ->first();

            // Préparer données à créer / mettre à jour
            $data = [
                'code' => $requete->code ?? 'CAPE-'.Str::upper(Str::random(6)),
                'name' => $capeName,
                'name_pomoter' => $promoteurFull ?? null,
                'firstname_pomoter' => $firstname_promoter ?? null,
                'email' => $email,
                'phone' => $phone,
                'address' => null,
                'target' => $cibles ? $cibles : null,
                'status' => 9,
                'type_cape_id' => 1, // obligatoire pour CAPE
                'district_id' => $district_id,
                'capacity' => $capacite,
                'town' => $commune,
                'observation' => "Tranche d'âge: " . ($tranche_age ?? 'N/A'),
                'promoter_id' => $this->defaultPromoterId,
            ];

            // Nettoyage: si on veut garder explicitement NULL pour clés non présents, retirez array_filter
            $data = array_filter($data, function ($v) {
                return !($v === null && $v !== 0);
            });

           
            if ($requete) {
                $requete->update($data);
            } else {
                Requete::create($data);
            }
        }
    }
}
