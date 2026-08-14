<?php

namespace App\Console\Commands;

use App\Models\Requete;
use App\Utilities\TextMatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Retire définitivement des centres importés à tort comme agréés.
 *
 * Corrections apportées à la liste MASM :
 *  - les CAPE de Bembèrèkè, autorisés par le MES comme internats et non agréés
 *    par le MASM ;
 *  - « Orphelinat XéwaSowè » (Glazoué), fermé depuis plus de deux ans.
 *
 * Le rapprochement se fait par nom (tolérant aux accents et fautes de frappe),
 * et strictement sur les dossiers issus de l'import (statut 9) : un dossier réel
 * de la plateforme ne peut jamais être supprimé par cette commande.
 */
class RemoveImportedRequetes extends Command
{
    protected $signature = 'requetes:remove
                            {--name=* : Nom(s) supplémentaire(s) à retirer, en plus de la liste par défaut}
                            {--force : Supprime réellement (sans cette option, simple simulation)}';

    protected $description = "Supprime des centres importés à tort comme agréés (par nom), avec leurs lignes filles";

    /** Centres à retirer, avec l'orthographe de la liste source. */
    private const EXCLUSIONS = [
        'Yénou Géo CAPE des Sœurs Filles du Cœur de Marie de Bembéréké',
        'CAPE Saint François d’Assise',
        'Saint François coll des Sœurs Dominicaine',
        'Bèssè ka Barouka de Paroisse notre Dame',
        'Orphelinat XéwaSowè',
    ];

    /** Tables filles qui référencent requetes (clés étrangères). */
    private const CHILD_TABLES = [
        'capes', 'requete_type_garderies', 'requete_files', 'parcours',
        'reponses', 'avis', 'affectations', 'referals', 'agendas',
    ];

    public function handle()
    {
        $targets = array_merge(self::EXCLUSIONS, $this->option('name'));

        // Candidats = uniquement les dossiers issus de l'import.
        $candidates = Requete::where('status', Requete::STATUS_AGREE_IMPORTE)
            ->pluck('id', 'name')->all();

        if ($candidates === []) {
            $this->info('Aucun dossier importé (statut 9) en base.');

            return 0;
        }

        $matched = [];
        foreach ($targets as $target) {
            $id = TextMatcher::bestMatch($target, $candidates, 0.80);
            if ($id) {
                $matched[$id] = $target;
            } else {
                $this->warn("Non trouvé en base : « $target ».");
            }
        }

        if ($matched === []) {
            $this->info('Aucun dossier à supprimer.');

            return 0;
        }

        $ids = array_keys($matched);
        $requetes = Requete::whereIn('id', $ids)->get(['id', 'code', 'name']);

        $this->warn($requetes->count().' dossier(s) seront SUPPRIMÉS définitivement :');
        $this->table(
            ['id', 'code', 'dénomination'],
            $requetes->map(fn ($r) => [$r->id, $r->code, mb_strimwidth($r->name, 0, 45, '…')])->all()
        );

        if (! $this->option('force')) {
            $this->info('Simulation — relancez avec --force pour supprimer.');

            return 0;
        }

        if (! $this->confirm('Confirmer la suppression définitive ?')) {
            $this->info('Annulé.');

            return 0;
        }

        DB::transaction(function () use ($ids) {
            // Les lignes filles partent d'abord (clés étrangères), puis les dossiers.
            foreach (self::CHILD_TABLES as $table) {
                DB::table($table)->whereIn('requete_id', $ids)->delete();
            }

            Requete::whereIn('id', $ids)->delete();
        });

        $this->info(count($ids).' dossier(s) supprimé(s).');

        return 0;
    }
}
