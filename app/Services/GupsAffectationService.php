<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Cps;
use App\Models\Parcours;
use App\Models\Requete;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Tient l'affectation des dossiers encore au niveau GUPS en phase avec le
 * rattachement de leur arrondissement.
 *
 * Au dépôt, un dossier est confié à l'agent du GUPS dont relève son
 * arrondissement, et la liste « À valider » d'un GUPS ne montre que les
 * dossiers qui lui sont confiés. Quand un arrondissement change de GUPS
 * (création d'un GUPS, redécoupage), qu'un dossier change d'arrondissement
 * ou qu'un agent est remplacé, rien ne déplaçait les dossiers en cours : ils
 * restaient chez l'ancien agent, invisibles pour le GUPS désormais compétent.
 */
class GupsAffectationService
{
    /** Statuts d'un dossier en instruction au GUPS, du dépôt à l'invitation. */
    public const STATUTS_GUPS = [0, 1, 2, 3, 4];

    /**
     * GUPS en charge du dossier.
     *
     * Plusieurs GUPS pouvant couvrir un même arrondissement, il ne se déduit
     * plus de l'arrondissement : il est porté par le dossier (`requetes.cps_id`).
     * Le GUPS par défaut de l'arrondissement ne sert que de repli pour un
     * dossier qui n'en porterait pas encore.
     */
    public function gupsId(Requete $requete): ?int
    {
        return $requete->cps_id ?? $requete->district?->cps_id;
    }

    /**
     * Agent (compte actif au rôle cps) du GUPS en charge du dossier.
     */
    public function agentCompetent(Requete $requete): ?User
    {
        $cpsId = $this->gupsId($requete);

        return $cpsId === null ? null : $this->agentDuGups($cpsId);
    }

    public function agentDuGups(int $cpsId): ?User
    {
        return User::role('cps')
            ->where('cps_id', $cpsId)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    /** Le GUPS couvre-t-il l'arrondissement du dossier ? */
    public function couvre(int $cpsId, ?int $districtId): bool
    {
        return $districtId !== null && DB::table('cps_district')
            ->where('cps_id', $cpsId)
            ->where('district_id', $districtId)
            ->exists();
    }

    /**
     * Autres GUPS vers lesquels le dossier peut être transféré : ceux qui
     * couvrent aussi son arrondissement. `has_agent` signale ceux qui n'ont pas
     * d'agent actif, vers lesquels le transfert sera refusé.
     */
    public function gupsCandidats(Requete $requete)
    {
        $actuel = $this->gupsId($requete);

        return Cps::whereHas('couverture', fn ($q) => $q->where('districts.id', $requete->district_id))
            ->when($actuel !== null, fn ($q) => $q->where('id', '!=', $actuel))
            ->orderBy('name')
            ->get(['id', 'name', 'acronym'])
            ->each(fn ($cps) => $cps->setAttribute('has_agent', $this->agentDuGups($cps->id) !== null));
    }

    /**
     * GUPS qui couvrent un arrondissement, pour choisir celui d'un dossier qui
     * y est transféré. `is_default` : GUPS par défaut de l'arrondissement ;
     * `has_agent` : au moins un agent actif pour recevoir le dossier.
     */
    public function gupsDeLArrondissement(int $districtId)
    {
        $defaut = DB::table('districts')->where('id', $districtId)->value('cps_id');

        return Cps::whereHas('couverture', fn ($q) => $q->where('districts.id', $districtId))
            ->orderBy('name')
            ->get(['id', 'name', 'acronym'])
            ->each(function ($cps) use ($defaut) {
                $cps->setAttribute('is_default', (int) $cps->id === (int) $defaut);
                $cps->setAttribute('has_agent', $this->agentDuGups($cps->id) !== null);
            });
    }

    /**
     * GUPS d'un dossier qui change d'arrondissement, faute de choix explicite :
     * le sien s'il couvre aussi le nouvel arrondissement, sinon le GUPS par
     * défaut de celui-ci, sinon l'unique GUPS qui le couvre.
     */
    public function gupsApresTransfert(Requete $requete, int $districtId): ?int
    {
        if ($requete->cps_id !== null && $this->couvre($requete->cps_id, $districtId)) {
            return $requete->cps_id;
        }

        $defaut = DB::table('districts')->where('id', $districtId)->value('cps_id');
        if ($defaut !== null) {
            return $defaut;
        }

        $couvrants = DB::table('cps_district')->where('district_id', $districtId)->pluck('cps_id');

        return $couvrants->count() === 1 ? $couvrants->first() : null;
    }

    /**
     * Transfère un dossier en instruction vers un autre GUPS qui couvre son
     * arrondissement, et le confie à l'agent de ce GUPS.
     *
     * @throws \DomainException si le transfert n'est pas possible (message affichable)
     */
    public function transferer(Requete $requete, Cps $cible, string $motif): User
    {
        if (! in_array((int) $requete->status, self::STATUTS_GUPS, true)) {
            throw new \DomainException("Le dossier n'est plus au niveau GUPS : il ne peut plus être transféré à un autre GUPS");
        }
        if ((int) $this->gupsId($requete) === (int) $cible->id) {
            throw new \DomainException('Le dossier est déjà pris en charge par ce GUPS');
        }
        if (! $this->couvre($cible->id, $requete->district_id)) {
            throw new \DomainException("Le {$cible->name} ne couvre pas l'arrondissement de ce dossier");
        }
        $agent = $this->agentDuGups($cible->id);
        if ($agent === null) {
            throw new \DomainException("Le {$cible->name} n'a pas d'agent actif pour recevoir le dossier");
        }

        $ancien = $requete->gups?->name ?? $requete->district?->cps?->name ?? 'GUPS non renseigné';
        $actuelle = $requete->affectation;

        DB::transaction(function () use ($requete, $actuelle, $agent, $cible, $ancien, $motif) {
            $requete->update(['cps_id' => $cible->id]);

            $actuelle?->update(['isLast' => false]);
            Affectation::create([
                'user_up' => $actuelle?->user_down ?? Auth::id(),
                'user_down' => $agent->id,
                'isLast' => true,
                'requete_id' => $requete->id,
                'sens' => $actuelle->sens ?? 1,
                'instruction' => $actuelle?->instruction,
            ]);

            Parcours::create([
                'libelle' => "Transfert de GUPS : $ancien → {$cible->name}. Motif : $motif",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        });

        $requete->unsetRelation('affectation');
        $requete->unsetRelation('gups');

        return $agent;
    }

    /**
     * Le dossier est-il au niveau GUPS mais détenu par quelqu'un qui n'est pas
     * un agent actif du GUPS de son arrondissement ?
     */
    public function estMalAffecte(Requete $requete): bool
    {
        if (! in_array((int) $requete->status, self::STATUTS_GUPS, true) || $requete->affectation === null) {
            return false;
        }

        $cpsId = $this->gupsId($requete);
        $detenteur = User::find($requete->affectation->user_down);

        return $cpsId !== null && ! (
            $detenteur !== null
            && $detenteur->is_active
            && (int) $detenteur->cps_id === (int) $cpsId
            && $detenteur->hasRole('cps')
        );
    }

    /**
     * Confie le dossier à l'agent du GUPS compétent s'il en est détenu par un
     * autre. Un dossier sans affectation n'est pas touché : il n'est jamais
     * entré dans le circuit.
     *
     * @return User|null le nouvel agent, ou null si rien n'a changé
     */
    public function reaffecter(Requete $requete, string $motif): ?User
    {
        if (! $this->estMalAffecte($requete)) {
            return null;
        }

        $agent = $this->agentCompetent($requete);
        if ($agent === null) {
            return null;
        }

        $actuelle = $requete->affectation;

        DB::transaction(function () use ($requete, $actuelle, $agent, $motif) {
            $actuelle->update(['isLast' => false]);

            Affectation::create([
                'user_up' => $actuelle->user_down,
                'user_down' => $agent->id,
                'isLast' => true,
                'requete_id' => $requete->id,
                // Un dossier renvoyé pour correction le reste : sa consigne suit.
                'sens' => $actuelle->sens ?? 1,
                'instruction' => $actuelle->instruction,
            ]);

            Parcours::create([
                'libelle' => 'Dossier confié au '.(Cps::whereKey($this->gupsId($requete))->value('name') ?? 'GUPS compétent')." ($motif)",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        });

        $requete->unsetRelation('affectation');

        return $agent;
    }

    /**
     * Destinataire attendu d'un dossier selon l'étape où il se trouve : le GUPS
     * de son arrondissement pendant l'instruction, la DDASM du département une
     * fois transmis, la DFEA ensuite. C'est la cible que le circuit lui aurait
     * donnée s'il l'avait suivi normalement.
     */
    public function destinataire(Requete $requete): ?User
    {
        $status = (int) $requete->status;

        if (in_array($status, self::STATUTS_GUPS, true)) {
            return $this->agentCompetent($requete);
        }

        if ($status === 5) {
            $departementId = $requete->district?->municipality?->department_id;

            return $departementId === null ? null : User::role('ddasm')
                ->where('department_id', $departementId)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
        }

        if (in_array($status, [6, 7], true)) {
            return User::role('dfea')->where('is_active', true)->orderBy('id')->first();
        }

        return null;
    }

    /**
     * Dossier jamais entré dans le circuit : déposé en ligne, mais dont la
     * création s'est interrompue avant l'affectation (voir le dépôt, rendu
     * atomique depuis). Personne ne le voit dans sa liste de travail.
     */
    public function estOrphelin(Requete $requete): bool
    {
        return (int) $requete->status <= 7 && $requete->affectation === null;
    }

    /**
     * Confie au destinataire attendu un dossier resté sans affectation.
     *
     * @return User|null l'agent destinataire, ou null si le dossier n'est pas
     *                   concerné ou qu'aucun destinataire n'est identifiable
     */
    public function reprendre(Requete $requete, string $motif): ?User
    {
        if (! $this->estOrphelin($requete)) {
            return null;
        }

        $agent = $this->destinataire($requete);
        if ($agent === null) {
            return null;
        }

        DB::transaction(function () use ($requete, $agent, $motif) {
            Affectation::create([
                'user_up' => $agent->id,
                'user_down' => $agent->id,
                'isLast' => true,
                'requete_id' => $requete->id,
                'sens' => 1,
            ]);

            Parcours::create([
                'libelle' => "Dossier repris et confié à son instance de traitement ($motif)",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        });

        $requete->unsetRelation('affectation');

        return $agent;
    }

    /**
     * Après un changement de couverture d'un arrondissement : les dossiers au
     * niveau GUPS dont le GUPS ne couvre plus l'arrondissement passent au GUPS
     * par défaut de celui-ci. Les dossiers d'un GUPS qui le couvre toujours ne
     * bougent pas, même si le GUPS par défaut a changé.
     *
     * @return int nombre de dossiers déplacés
     */
    public function reaffecterArrondissement(int $districtId, string $motif): int
    {
        $defaut = DB::table('districts')->where('id', $districtId)->value('cps_id');

        return Requete::with(['district.cps', 'affectation'])
            ->where('district_id', $districtId)
            ->whereIn('status', self::STATUTS_GUPS)
            ->get()
            ->filter(function ($requete) use ($defaut, $districtId, $motif) {
                if ($requete->cps_id === null || ! $this->couvre($requete->cps_id, $districtId)) {
                    $requete->update(['cps_id' => $defaut]);
                }
                return $this->reaffecter($requete, $motif) !== null;
            })
            ->count();
    }
}
