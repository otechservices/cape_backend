<?php

namespace App\Console\Commands;

use App\Models\Requete;
use App\Services\GupsAffectationService;
use Illuminate\Console\Command;

class ReprendreDossiersOrphelins extends Command
{
    protected $signature = 'requetes:reprendre-orphelins {--force : Reprend réellement (sans cette option, simple simulation)}';

    protected $description = "Confie à leur instance de traitement les dossiers déposés en ligne restés sans affectation";

    /**
     * Rattrape les dépôts interrompus avant la création de l'affectation : le
     * dossier et ses pièces existent, mais personne ne l'a dans sa liste de
     * travail. Le destinataire dépend du statut : GUPS de l'arrondissement
     * pendant l'instruction, DDASM du département une fois transmis, DFEA ensuite.
     */
    public function handle(GupsAffectationService $service)
    {
        $dossiers = Requete::with(['district.cps', 'district.Municipality', 'affectation'])
            ->where('status', '<=', 7)
            ->get()
            ->filter(fn ($requete) => $service->estOrphelin($requete))
            ->values();

        if ($dossiers->isEmpty()) {
            $this->info('Aucun dossier orphelin : tous sont confiés à une instance.');

            return 0;
        }

        $sansDestinataire = 0;
        $this->table(
            ['id', 'code', 'centre', 'statut', 'arrondissement', 'sera confié à'],
            $dossiers->map(function ($requete) use ($service, &$sansDestinataire) {
                $agent = $service->destinataire($requete);
                $sansDestinataire += $agent === null ? 1 : 0;

                return [
                    $requete->id,
                    $requete->code,
                    mb_strimwidth($requete->name, 0, 40, '…'),
                    $requete->status,
                    $requete->district?->name,
                    $agent?->email ?? 'AUCUN DESTINATAIRE',
                ];
            })
        );

        $this->line('Vérifiez cette liste : les essais et les doublons gagnent à être supprimés plutôt que repris.');

        if ($sansDestinataire > 0) {
            $this->warn("$sansDestinataire dossier(s) sans destinataire identifiable : arrondissement non rattaché, ou aucun compte actif dans l'instance concernée.");
        }

        if (! $this->option('force')) {
            $this->comment('Simulation : relancez avec --force pour reprendre ces dossiers.');

            return 0;
        }

        $repris = $dossiers
            ->filter(fn ($requete) => $service->reprendre($requete, 'dépôt interrompu, dossier sans affectation') !== null)
            ->count();

        $this->info("$repris dossier(s) repris.");

        return 0;
    }
}
