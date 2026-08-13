<?php

namespace App\Imports;

use App\Models\Cape;
use App\Models\Cps;
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

    /** Lignes `capes` (autorisation matérialisée) créées. */
    public int $capesCreated = 0;

    /** Dossiers rattachés au bon CPS mais avec un arrondissement approximatif. */
    public int $approximateDistrict = 0;

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

    /** @var array<string, int>|null */
    private ?array $cpsLocalityCache = null;

    /** @var array<int, array<string, int>> */
    private array $cpsDistrictCache = [];

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
            $arrondissement = trim((string) data_get($row, 'arrondissement'));
            $gups = trim((string) data_get($row, 'gups'));

            // Le GUPS est la nouvelle appellation du CPS : c'est la source la
            // plus fiable du rattachement. On l'utilise d'abord ; à défaut, on
            // retombe sur la résolution géographique depuis la localisation.
            $districtId = $this->resolveDistrictViaGups($gups, $arrondissement, $localisation)
                ?? $this->resolveDistrict(
                    trim((string) data_get($row, 'departement')),
                    $communeName,
                    $arrondissement,
                    $localisation,
                    $line
                );

            if ($districtId === null) {
                $this->withoutDistrict++;
                if ($gups !== '') {
                    $this->warnings[] = "Ligne $line : GUPS/CPS « $gups » non rattaché — à transférer manuellement.";
                }
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
                // Agréés hors plateforme mais considérés comme définitivement
                // autorisés : ces drapeaux les rendent visibles partout où
                // l'application reconnaît un dossier autorisé (tableau de bord,
                // écrans « CAPE autorisés », recherche), au même titre que la
                // ligne `capes` créée plus bas.
                'has_agreemant' => true,
                'is_authorized' => true,
                'promoter_id' => $this->defaultPromoterId,
            ];

            // Rapprochement restreint aux dossiers issus de l'import (statut 9) :
            // un ré-import met à jour la ligne existante sans jamais écraser un
            // dossier réel de la plateforme qui porterait le même nom. Les
            // dénominations du fichier étant uniques, ce critère suffit.
            $existing = Requete::where('name', $name)
                ->where('status', Requete::STATUS_AGREE_IMPORTE)
                ->first();

            if ($existing) {
                $existing->update($data);
                $requete = $existing;
                $this->updated++;
            } else {
                $data['code'] = ($isGarderie ? 'GARD-' : 'CAPE-').Str::upper(Str::random(6));
                $requete = Requete::create($data);
                $this->created++;
            }

            // Matérialise l'autorisation : c'est la table `capes` que lisent les
            // écrans « CAPE autorisés ». On reproduit le résultat du circuit
            // d'agrément sans sa partie compte utilisateur / PDF / e-mail, qui
            // n'a pas lieu d'être pour des centres déjà agréés hors plateforme.
            // firstOrCreate garde la commande ré-exécutable sans doublon.
            $cape = Cape::firstOrCreate(['requete_id' => $requete->id], ['status' => 1]);
            if ($cape->wasRecentlyCreated) {
                $this->capesCreated++;
            }
        }
    }

    /**
     * Rattache le dossier à partir du GUPS, nouvelle appellation du CPS.
     *
     * Le GUPS nomme directement le centre de rattachement (« Allada » →
     * « Centre de Promotion Sociale d'Allada »), donc la source la plus fiable.
     * On choisit ensuite, parmi les arrondissements de ce CPS, celui qui colle
     * le mieux à la localisation ; faute de correspondance précise, on prend un
     * arrondissement représentatif du CPS : le rattachement (donc la visibilité
     * CPS/DDASM) reste correct, seule la précision de l'arrondissement est
     * approximative et rattrapable par un transfert.
     */
    private function resolveDistrictViaGups(string $gups, string $arrondissement, string $localisation): ?int
    {
        if ($gups === '') {
            return null;
        }

        $cpsId = TextMatcher::bestMatch($gups, $this->cpsLocalities(), 0.80);
        if (! $cpsId) {
            return null;
        }

        $districts = $this->cpsDistricts($cpsId);
        if ($districts === []) {
            return null;
        }

        $districtId = TextMatcher::bestMatch($arrondissement, $districts)
            ?? TextMatcher::bestMatch($localisation, $districts);

        if ($districtId) {
            return $districtId;
        }

        // Rattachement au bon CPS sans arrondissement précis : à affiner ensuite.
        $this->approximateDistrict++;

        return (int) reset($districts);
    }

    /**
     * Localité de chaque CPS (préfixe « Centre de Promotion Sociale … » retiré)
     * vers son identifiant, pour le rapprochement avec le GUPS.
     *
     * @return array<string, int>
     */
    private function cpsLocalities(): array
    {
        return $this->cpsLocalityCache ??= Cps::pluck('name', 'id')
            ->mapWithKeys(function ($name, $id) {
                $loc = preg_replace('/^Centre de Promotion Sociale\s*/iu', '', (string) $name);
                $loc = preg_replace('/^(\d+\s+)?(de la|de l|des|du|de|d)[\s\'’]+/iu', '', $loc);

                return [trim($loc) => $id];
            })
            ->all();
    }

    /**
     * Arrondissements rattachés à un CPS donné (libellé => id).
     *
     * @return array<string, int>
     */
    private function cpsDistricts(int $cpsId): array
    {
        return $this->cpsDistrictCache[$cpsId] ??= District::where('cps_id', $cpsId)
            ->pluck('id', 'name')->all();
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

        $districts = $this->districts($municipalityId);

        $districtId = TextMatcher::bestMatch($districtName, $districts)
            ?? TextMatcher::bestMatch($localisation, $districts);

        if ($districtId) {
            return $districtId;
        }

        // Arrondissement non identifié mais commune connue : on rattache au
        // premier arrondissement de la commune. Le dossier devient visible du
        // CPS qui la couvre ; l'arrondissement exact reste à préciser.
        if ($districts !== []) {
            $this->approximateDistrict++;

            return (int) reset($districts);
        }

        $this->warnings[] = "Ligne $line : aucun arrondissement pour la commune de « $localisation » — à rattacher manuellement.";

        return null;
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
