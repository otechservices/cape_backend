<?php

namespace App\Services;

use App\Models\Affectation;
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
     * Agent (compte actif au rôle cps) du GUPS dont relève l'arrondissement.
     */
    public function agentCompetent(Requete $requete): ?User
    {
        $cpsId = $requete->district?->cps_id;
        if ($cpsId === null) {
            return null;
        }

        return User::role('cps')
            ->where('cps_id', $cpsId)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
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

        $cpsId = $requete->district?->cps_id;
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
                'libelle' => 'Dossier confié au '.($requete->district->cps->name ?? 'GUPS compétent')." ($motif)",
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
     * Réaffecte les dossiers au niveau GUPS d'un arrondissement.
     *
     * @return int nombre de dossiers déplacés
     */
    public function reaffecterArrondissement(int $districtId, string $motif): int
    {
        return Requete::with(['district.cps', 'affectation'])
            ->where('district_id', $districtId)
            ->whereIn('status', self::STATUTS_GUPS)
            ->get()
            ->filter(fn ($requete) => $this->reaffecter($requete, $motif) !== null)
            ->count();
    }
}
