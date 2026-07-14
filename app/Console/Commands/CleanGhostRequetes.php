<?php

namespace App\Console\Commands;

use App\Models\Requete;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanGhostRequetes extends Command
{
    protected $signature = 'requetes:clean-ghosts {--force : Supprime réellement (sans cette option, simple simulation)}';

    protected $description = "Supprime les requêtes fantômes d'un import raté : dénomination 'N/A', aucune donnée métier, aucune relation";

    /**
     * Champs qui portent une information métier saisie ou importée.
     * `observation` est volontairement exclu : l'import y écrit la constante
     * "Tranche d'âge: N/A", qui ne constitue pas une donnée.
     */
    private const BUSINESS_FIELDS = [
        'email', 'phone', 'address', 'name_pomoter', 'firstname_pomoter',
        'district_id', 'capacity', 'town', 'target', 'name_chief', 'phone_chief',
        'aggreement_reference', 'has_agreemant', 'is_authorized',
    ];

    private const RELATIONS = [
        'files', 'files2', 'parcours', 'reponses', 'avis', 'affectations', 'referals', 'Cape',
    ];

    /**
     * Une requête fantôme porte le nom 'N/A', ne contient aucune donnée métier
     * et n'a aucune relation. Toute ligne qui possède ne serait-ce qu'un
     * téléphone, un district ou un fichier est conservée : elle provient d'un
     * import partiel et doit être ré-importée, pas supprimée.
     */
    private function ghostQuery()
    {
        $query = Requete::query()->where('name', 'N/A');

        foreach (self::BUSINESS_FIELDS as $field) {
            $query->whereNull($field);
        }

        foreach (self::RELATIONS as $relation) {
            $query->whereDoesntHave($relation);
        }

        return $query;
    }

    /**
     * Lignes 'N/A' qui portent des données : à corriger par ré-import.
     */
    private function recoverableQuery()
    {
        return Requete::query()
            ->where('name', 'N/A')
            ->whereNotIn('id', $this->ghostQuery()->select('id'));
    }

    public function handle()
    {
        $ghosts = $this->ghostQuery()->count();
        $recoverable = $this->recoverableQuery()->count();

        if ($recoverable > 0) {
            $this->warn("$recoverable requête(s) sans dénomination mais porteuses de données : CONSERVÉES.");
            $this->line('  → elles doivent être corrigées par un ré-import du fichier source.');
            $this->newLine();
        }

        if ($ghosts === 0) {
            $this->info('Aucune requête fantôme à supprimer.');

            return 0;
        }

        $this->warn("$ghosts requête(s) fantôme(s) : aucune donnée métier, aucune relation.");
        $this->table(
            ['id', 'code', 'name', 'status', 'created_at'],
            $this->ghostQuery()->take(10)->get(['id', 'code', 'name', 'status', 'created_at'])->toArray()
        );

        if (! $this->option('force')) {
            $this->info("Simulation — relancez avec --force pour supprimer ces $ghosts lignes.");

            return 0;
        }

        if (! $this->confirm("Confirmer la suppression définitive de $ghosts requête(s) ?")) {
            $this->info('Annulé.');

            return 0;
        }

        $deleted = DB::transaction(fn () => $this->ghostQuery()->delete());

        $this->info("$deleted requête(s) fantôme(s) supprimée(s).");

        return 0;
    }
}
