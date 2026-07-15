<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Requete;
use App\Models\RequeteTypeGarderie;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Purge le premier import des agréments existants, avant de le rejouer proprement.
 *
 * L'ancien import a laissé deux résidus :
 *  - des requêtes en statut 9 mal typées (service_id jamais renseigné) et
 *    massivement sans dénomination ;
 *  - des arrondissements fabriqués à partir du texte libre de localisation
 *    ("Agori, Abomey-Calavi", "Pascal,"...), sans commune ni CPS rattaché.
 */
class PurgeOldImport extends Command
{
    protected $signature = 'requetes:purge-import {--force : Supprime réellement (sans cette option, simple simulation)}';

    protected $description = "Purge les requêtes et arrondissements issus de l'ancien import des agréments";

    private const RELATIONS = [
        'files', 'files2', 'parcours', 'reponses', 'avis', 'affectations', 'referals', 'Cape',
    ];

    /**
     * Requêtes de l'ancien import : statut 9, jamais rattachées à un promoteur
     * réel et sans aucune donnée produite dans la plateforme. Toute requête
     * portant une relation est conservée.
     */
    private function requeteQuery()
    {
        $query = Requete::query()
            ->where('status', Requete::STATUS_AGREE_IMPORTE)
            ->whereNull('session_id');

        foreach (self::RELATIONS as $relation) {
            $query->whereDoesntHave($relation);
        }

        return $query;
    }

    /**
     * Arrondissements fabriqués par l'ancien import : sans commune de
     * rattachement, donc rattachés à aucun CPS.
     */
    private function districtQuery()
    {
        return District::query()
            ->whereNull('municipality_id')
            ->whereNull('cps_id')
            ->whereDoesntHave('districtRequetes');
    }

    public function handle()
    {
        $requetes = $this->requeteQuery()->count();
        $conserved = Requete::where('status', Requete::STATUS_AGREE_IMPORTE)->count() - $requetes;
        $districts = $this->districtQuery()->count();

        $this->warn("$requetes requête(s) en statut 9 à supprimer.");
        if ($conserved > 0) {
            $this->line("  $conserved requête(s) en statut 9 CONSERVÉE(S) : elles portent une relation ou une session.");
        }
        $this->warn("$districts arrondissement(s) fabriqué(s) à supprimer (sans commune ni CPS).");

        if ($requetes === 0 && $districts === 0) {
            $this->info('Rien à purger.');

            return 0;
        }

        if (! $this->option('force')) {
            $this->info('Simulation — relancez avec --force pour purger.');

            return 0;
        }

        if (! $this->confirm('Confirmer la purge définitive ?')) {
            $this->info('Annulé.');

            return 0;
        }

        DB::transaction(function () {
            // Les sous-types de garderie sont une donnée propre à la requête,
            // créée à l'import : ils doivent partir avec elle. On les supprime
            // d'abord, sinon leur clé étrangère bloque la suppression.
            $ids = $this->requeteQuery()->pluck('id');
            RequeteTypeGarderie::whereIn('requete_id', $ids)->delete();

            // Puis les requêtes, avant les arrondissements qu'elles référencent.
            $this->requeteQuery()->delete();
            $this->districtQuery()->delete();
        });

        $this->info("Purge terminée : $requetes requête(s) et $districts arrondissement(s) supprimés.");

        return 0;
    }
}
