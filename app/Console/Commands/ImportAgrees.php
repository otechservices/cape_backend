<?php

namespace App\Console\Commands;

use App\Imports\AgreesImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportAgrees extends Command
{
    protected $signature = 'import:agrees
                            {file=storage/app/imports/capes_agrees_a_valider.csv : Fichier CSV/Excel validé}
                            {--promoter_id=1 : Promoteur par défaut, en attendant le rattachement réel}
                            {--force : Écrit réellement en base (sans cette option, simple simulation)}';

    protected $description = 'Importe la liste des CAPE / Garderies agréés hors plateforme';

    public function handle()
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("Fichier introuvable : $file");

            return 1;
        }

        $import = new AgreesImport((int) $this->option('promoter_id'));

        // En simulation, l'import s'exécute puis la transaction est annulée :
        // les compteurs sont donc exacts, sans rien écrire en base.
        DB::beginTransaction();

        try {
            Excel::import($import, $file);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Erreur durant l\'import : '.$e->getMessage());

            return 1;
        }

        $this->newLine();
        $this->info("Créées  : {$import->created}");
        $this->info("Mises à jour : {$import->updated}");
        $this->info("Ignorées : {$import->skipped}");

        if ($import->createdTypes) {
            $this->newLine();
            $this->info('Sous-catégories créées : '.implode(', ', array_unique($import->createdTypes)));
        }

        if ($import->withoutDistrict > 0) {
            $this->newLine();
            $this->warn("{$import->withoutDistrict} dossier(s) sans arrondissement.");
            $this->line('  → invisibles pour les CPS et DDASM ; à rattacher via « Transférer » depuis Recherche & export.');
        }

        if ($import->warnings) {
            $this->newLine();
            $this->warn('Avertissements :');
            foreach (array_slice($import->warnings, 0, 15) as $warning) {
                $this->line("  - $warning");
            }
            if (count($import->warnings) > 15) {
                $this->line('  … '.(count($import->warnings) - 15).' autre(s).');
            }
        }

        if (! $this->option('force')) {
            DB::rollBack();
            $this->newLine();
            $this->info('Simulation — aucune écriture. Relancez avec --force pour importer.');

            return 0;
        }

        DB::commit();
        $this->newLine();
        $this->info('Import terminé.');

        return 0;
    }
}
