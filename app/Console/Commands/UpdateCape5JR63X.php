<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Mise à jour complète et reproductible du dossier CAPE-5JR63X
 * (Foyer Jean Martin Moyé) : coordonnées de géolocalisation et fichiers.
 *
 * Tout est intégré (code, coordonnées, dossier source), afin de lancer la même
 * commande à l'identique en local et sur le serveur. Le dossier source est
 * résolu via public_path('cc'), donc portable d'un environnement à l'autre.
 *
 * Délègue à `dossier:update`, qui remplace chaque fichier dont la rubrique
 * existe déjà et ajoute les autres, sans toucher aux rubriques non concernées.
 */
class UpdateCape5JR63X extends Command
{
    protected $signature = 'dossier:update-5jr63x {--force : Applique réellement (sans cette option, simulation)}';

    protected $description = 'Met à jour le dossier CAPE-5JR63X : coordonnées + fichiers de public/cc';

    public function handle()
    {
        $source = public_path('cc');

        if (! is_dir($source)) {
            $this->error("Dossier source introuvable : $source");
            $this->line('Placez les fichiers dans public/cc avant de relancer.');

            return 1;
        }

        $params = [
            'code' => 'CAPE-5JR63X',
            '--coords' => '9.85497,2.72072',
            '--files' => $source,
        ];

        // N'ajouter le drapeau que s'il est actif : présent, il vaut toujours vrai.
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        return $this->call('dossier:update', $params);
    }
}
