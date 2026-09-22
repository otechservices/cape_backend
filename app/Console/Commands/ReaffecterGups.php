<?php

namespace App\Console\Commands;

use App\Models\Requete;
use App\Models\User;
use App\Services\GupsAffectationService;
use Illuminate\Console\Command;

class ReaffecterGups extends Command
{
    protected $signature = 'requetes:reaffecter-gups {--force : Réaffecte réellement (sans cette option, simple simulation)}';

    protected $description = "Confie au GUPS de leur arrondissement les dossiers en cours restés chez l'agent d'un autre GUPS";

    /**
     * Rattrape les dossiers déposés avant un changement de GUPS de leur
     * arrondissement (ou le remplacement d'un agent) : ils restaient dans la
     * liste « À valider » de l'ancien agent, invisibles pour le GUPS compétent.
     */
    public function handle(GupsAffectationService $service)
    {
        $dossiers = Requete::with(['district.cps', 'affectation'])
            ->whereIn('status', GupsAffectationService::STATUTS_GUPS)
            ->get()
            ->filter(fn ($requete) => $service->estMalAffecte($requete))
            ->values();

        if ($dossiers->isEmpty()) {
            $this->info('Aucun dossier à réaffecter : tous sont chez le GUPS de leur arrondissement.');

            return 0;
        }

        $sansAgent = 0;
        $this->table(
            ['id', 'code', 'centre', 'arrondissement', 'détenu par', 'GUPS compétent'],
            $dossiers->map(function ($requete) use ($service, &$sansAgent) {
                $agent = $service->agentCompetent($requete);
                $sansAgent += $agent === null ? 1 : 0;

                return [
                    $requete->id,
                    $requete->code,
                    mb_strimwidth($requete->name, 0, 45, '…'),
                    $requete->district?->name,
                    User::find($requete->affectation->user_down)?->email ?? 'compte supprimé',
                    $agent === null
                        ? 'AUCUN AGENT ACTIF'
                        : $requete->district->cps->name.' ('.$agent->email.')',
                ];
            })
        );

        if ($sansAgent > 0) {
            $this->warn("$sansAgent dossier(s) sans agent actif dans leur GUPS : créez ou réactivez le compte, puis relancez.");
        }

        if (! $this->option('force')) {
            $this->comment('Simulation : relancez avec --force pour réaffecter.');

            return 0;
        }

        $deplaces = $dossiers
            ->filter(fn ($requete) => $service->reaffecter($requete, "réaffectation au GUPS de l'arrondissement") !== null)
            ->count();

        $this->info("$deplaces dossier(s) réaffecté(s).");

        return 0;
    }
}
