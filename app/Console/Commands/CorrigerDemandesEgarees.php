<?php

namespace App\Console\Commands;

use App\Models\Cps;
use App\Models\Requete;
use App\Models\User;
use App\Services\GupsAffectationService;
use Illuminate\Console\Command;

class CorrigerDemandesEgarees extends Command
{
    protected $signature = 'requetes:corriger-egarees
        {--code= : Ne traiter qu\'un dossier (ex. Garderie-418XJJ)}
        {--force : Corrige réellement (sans cette option, simple simulation)}';

    protected $description = "Remet dans le bon circuit les demandes égarées : sans GUPS valide, sans affectation, ou chez le mauvais agent";

    /**
     * Point d'entrée unique pour les dossiers que personne ne voit ou qui sont
     * chez le mauvais interlocuteur. Trois passes, dans cet ordre car chacune
     * prépare la suivante :
     *  1. GUPS du dossier absent, ou qui ne couvre pas son arrondissement :
     *     le dossier prend le GUPS par défaut de l'arrondissement ;
     *  2. dossier sans affectation (dépôt interrompu) : confié à l'instance
     *     attendue pour son statut ;
     *  3. dossier au niveau GUPS détenu par quelqu'un d'autre qu'un agent actif
     *     de son GUPS : confié à cet agent.
     * Un dossier transféré à un autre GUPS qui couvre son arrondissement n'est
     * pas touché.
     */
    public function handle(GupsAffectationService $service)
    {
        $force = (bool) $this->option('force');
        $code = $this->option('code');

        $dossiers = Requete::with(['district.cps', 'district.Municipality', 'gups', 'affectation'])
            ->where('status', '<=', 7)
            ->when($code, fn ($q) => $q->where('code', $code))
            ->get();

        if ($code && $dossiers->isEmpty()) {
            $this->error("Aucun dossier en cours avec le code $code (dossier inexistant, ou déjà au-delà de l'étape DFEA).");

            return 1;
        }

        $corriges = $this->passeGups($service, $dossiers, $force)
            + $this->passeOrphelins($service, $dossiers, $force)
            + $this->passeMalAffectes($service, $dossiers, $force);

        $this->newLine();
        if (! $force) {
            $this->comment('Simulation : rien n\'a été modifié. Relancez avec --force pour corriger.');
        } else {
            $this->info("$corriges correction(s) appliquée(s).");
        }

        return 0;
    }

    private function passeGups(GupsAffectationService $service, $dossiers, bool $force): int
    {
        $cas = $dossiers->filter(fn ($r) => $r->district_id !== null
            && ($r->cps_id === null || ! $service->couvre($r->cps_id, $r->district_id)))->values();

        $this->titre('1. GUPS du dossier absent ou hors de son arrondissement', $cas->count());
        if ($cas->isEmpty()) {
            return 0;
        }

        $this->table(['code', 'centre', 'arrondissement', 'GUPS actuel', 'nouveau GUPS'], $cas->map(fn ($r) => [
            $r->code,
            mb_strimwidth($r->name, 0, 40, '…'),
            $r->district?->name,
            $r->gups?->name ?? '—',
            $r->district?->cps?->name ?? 'AUCUN : arrondissement sans GUPS par défaut',
        ]));

        if (! $force) {
            return 0;
        }

        return $cas->filter(function ($r) {
            if ($r->district?->cps_id === null) {
                return false;
            }
            $r->update(['cps_id' => $r->district->cps_id]);
            $r->unsetRelation('gups');

            return true;
        })->count();
    }

    private function passeOrphelins(GupsAffectationService $service, $dossiers, bool $force): int
    {
        $cas = $dossiers->filter(fn ($r) => $service->estOrphelin($r))->values();

        $this->titre('2. Dossiers sans affectation (personne ne les voit)', $cas->count());
        if ($cas->isEmpty()) {
            return 0;
        }

        $this->table(['code', 'centre', 'statut', 'sera confié à'], $cas->map(fn ($r) => [
            $r->code,
            mb_strimwidth($r->name, 0, 40, '…'),
            $r->status,
            $service->destinataire($r)?->email ?? 'AUCUN DESTINATAIRE (compte manquant ou inactif)',
        ]));

        return $force
            ? $cas->filter(fn ($r) => $service->reprendre($r, 'demande égarée, dossier sans affectation') !== null)->count()
            : 0;
    }

    private function passeMalAffectes(GupsAffectationService $service, $dossiers, bool $force): int
    {
        $cas = $dossiers->filter(fn ($r) => $service->estMalAffecte($r))->values();

        $this->titre('3. Dossiers au GUPS détenus par le mauvais agent', $cas->count());
        if ($cas->isEmpty()) {
            return 0;
        }

        $this->table(['code', 'centre', 'détenu par', 'sera confié à'], $cas->map(function ($r) use ($service) {
            $agent = $service->agentCompetent($r);

            return [
                $r->code,
                mb_strimwidth($r->name, 0, 40, '…'),
                User::find($r->affectation->user_down)?->email ?? 'compte supprimé',
                $agent
                    ? (Cps::whereKey($service->gupsId($r))->value('name')).' ('.$agent->email.')'
                    : 'AUCUN AGENT ACTIF dans le GUPS',
            ];
        }));

        return $force
            ? $cas->filter(fn ($r) => $service->reaffecter($r, 'demande égarée, confiée au GUPS du dossier') !== null)->count()
            : 0;
    }

    private function titre(string $titre, int $nombre): void
    {
        $this->newLine();
        $nombre === 0
            ? $this->line("<info>✔</info> $titre : aucun")
            : $this->line("<comment>●</comment> $titre : $nombre");
    }
}
