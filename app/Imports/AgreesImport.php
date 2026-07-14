<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\District;
use App\Models\Municipality;
use App\Models\Requete;
use App\Models\TypeCape;
use App\Utilities\TextMatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import de la liste des CAPE / Garderies agréés hors plateforme.
 *
 * Contrairement aux imports précédents, cette classe :
 *  - renseigne `service_id` (1 = CAPE, 2 = Garderie), qui porte la vraie
 *    distinction, au lieu de détourner `type_cape_id` ;
 *  - ne crée JAMAIS d'arrondissement : un libellé introuvable laisse
 *    `district_id` à NULL plutôt que de fabriquer une entrée fantôme ;
 *  - déduplique sur dénomination + commune, afin de conserver les homonymes
 *    situés dans deux communes différentes ;
 *  - ignore toute ligne sans dénomination.
 *
 * Les dossiers sont créés au statut 9 : agréés hors système, en attente de
 * régularisation par le promoteur puis de validation par le DFEA.
 */
class AgreesImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public int $withoutDistrict = 0;

    /** @var string[] */
    public array $warnings = [];

    /** Sous-catégories absentes du référentiel et créées à la volée. */
    public array $createdTypes = [];

    /** @var array<string, int>|null */
    private ?array $departmentCache = null;

    /** @var array<int, array<string, int>> */
    private array $municipalityCache = [];

    /** @var array<int, array<string, int>> */
    private array $districtCache = [];

    public function __construct(private int $defaultPromoterId = 1) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $line = $i + 2; // +1 pour l'en-tête, +1 pour un index humain

            $name = trim((string) data_get($row, 'denomination'));
            if ($name === '') {
                $this->skipped++;
                $this->warnings[] = "Ligne $line ignorée : dénomination absente.";
                continue;
            }

            $isGarderie = strcasecmp(trim((string) data_get($row, 'service')), 'garderie') === 0;

            $communeName = trim((string) data_get($row, 'commune'));
            $localisation = trim((string) data_get($row, 'localisation'));

            $districtId = $this->resolveDistrict(
                trim((string) data_get($row, 'departement')),
                $communeName,
                trim((string) data_get($row, 'arrondissement')),
                $localisation,
                $line
            );

            if ($districtId === null) {
                $this->withoutDistrict++;
            }

            $phone = trim((string) data_get($row, 'telephone1')) ?: null;
            $phone2 = trim((string) data_get($row, 'telephone2')) ?: null;

            $data = [
                'name' => $name,
                'service_id' => $isGarderie ? 2 : 1,
                'type_cape_id' => $isGarderie ? 5 : $this->resolveTypeCape(data_get($row, 'type_cape')),
                'district_id' => $districtId,
                'town' => $communeName ?: null,
                'address' => trim((string) data_get($row, 'localisation')) ?: null,
                'phone' => $phone,
                'phone_pomoter' => $phone2,
                'email' => trim((string) data_get($row, 'email')) ?: null,
                'name_pomoter' => trim((string) data_get($row, 'nom_promoteur')) ?: null,
                'aggreement_reference' => trim((string) data_get($row, 'reference_agrement')) ?: null,
                'aggreement_year' => trim((string) data_get($row, 'annee_agrement')) ?: null,
                'status' => Requete::STATUS_AGREE_IMPORTE,
                'promoter_id' => $this->defaultPromoterId,
            ];

            // Rapprochement sur dénomination + commune : deux centres homonymes
            // situés dans des communes différentes restent bien distincts.
            $existing = Requete::where('name', $name)
                ->where(fn ($q) => $communeName !== ''
                    ? $q->where('town', $communeName)
                    : $q->whereNull('town'))
                ->first();

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                $data['code'] = ($isGarderie ? 'GARD-' : 'CAPE-').Str::upper(Str::random(6));
                Requete::create($data);
                $this->created++;
            }
        }
    }

    /**
     * Retrouve l'arrondissement par rapprochement textuel, sans jamais rien
     * créer : un arrondissement fabriqué à partir du texte libre serait
     * rattaché à aucun CPS et rendrait le dossier invisible.
     *
     * La colonne `arrondissement` est privilégiée ; à défaut on cherche dans la
     * localisation brute. Un ciblage imparfait reste rattrapable : le dossier
     * peut être transféré vers le bon arrondissement depuis le back-office.
     */
    private function resolveDistrict(
        string $departmentName,
        string $communeName,
        string $districtName,
        string $localisation,
        int $line
    ): ?int {
        $departmentId = TextMatcher::bestMatch($departmentName, $this->departments());
        if (! $departmentId) {
            $this->warnings[] = "Ligne $line : département « $departmentName » inconnu.";

            return null;
        }

        // La commune peut être nommée explicitement ou noyée dans la localisation.
        $municipalityId = TextMatcher::bestMatch($communeName, $this->municipalities($departmentId))
            ?? TextMatcher::bestMatch($localisation, $this->municipalities($departmentId));

        if (! $municipalityId) {
            $this->warnings[] = "Ligne $line : commune introuvable pour « $localisation » ($departmentName).";

            return null;
        }

        $districtId = TextMatcher::bestMatch($districtName, $this->districts($municipalityId))
            ?? TextMatcher::bestMatch($localisation, $this->districts($municipalityId));

        if (! $districtId) {
            $this->warnings[] = "Ligne $line : arrondissement introuvable pour « $localisation » — à transférer manuellement.";

            return null;
        }

        return $districtId;
    }

    /**
     * Sous-catégorie (table type_capes), rapprochée textuellement.
     * Un libellé inconnu est créé : le référentiel se complète au fil des imports.
     */
    private function resolveTypeCape($label): ?int
    {
        $label = trim((string) $label);
        if ($label === '') {
            return null;
        }

        $types = TypeCape::pluck('id', 'name')->all();

        $id = TextMatcher::bestMatch($label, $types);
        if ($id) {
            return $id;
        }

        $created = TypeCape::create(['name' => $label, 'is_active' => 1]);
        $this->createdTypes[] = $label;

        return $created->id;
    }

    /** @return array<string, int> */
    private function departments(): array
    {
        return $this->departmentCache ??= Department::pluck('id', 'name')->all();
    }

    /** @return array<string, int> */
    private function municipalities(int $departmentId): array
    {
        return $this->municipalityCache[$departmentId] ??= Municipality::where('department_id', $departmentId)
            ->pluck('id', 'name')->all();
    }

    /**
     * Seuls les arrondissements réellement rattachés à une commune sont
     * candidats : les entrées orphelines ne mènent à aucun CPS.
     *
     * @return array<string, int>
     */
    private function districts(int $municipalityId): array
    {
        return $this->districtCache[$municipalityId] ??= District::where('municipality_id', $municipalityId)
            ->pluck('id', 'name')->all();
    }
}
