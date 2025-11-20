<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CapesImport;


class ImportCape extends Command
{
  protected $signature = 'import:capes {file : Chemin vers le fichier Excel} {--promoter_id=1 : Default promoter_id}';
    protected $description = 'Importer/mettre à jour les capes depuis un fichier Excel et fixer type_cape_id = 1';


  
    public function handle()
    {
        $file = $this->argument('file');
        $promoterId = $this->option('promoter_id') ?? 1;

        if (!file_exists($file)) {
            $this->error("Fichier introuvable: $file");
            return 1;
        }

        $this->info("Import en cours depuis : $file ...");

        try {
            Excel::import(new CapesImport($promoterId), $file);
            $this->info('Import terminé avec succès.');
        } catch (\Exception $e) {
            $this->error('Erreur durant l\'import : ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
