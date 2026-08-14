<?php

namespace App\Console\Commands;

use App\Models\Requete;
use App\Models\RequeteFile;
use App\Utilities\TextMatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Met à jour un dossier : coordonnées de géolocalisation et/ou fichiers.
 *
 * Les fichiers du dossier source sont nommés d'après leur rubrique
 * (« liste du personnel.pdf », « acte de donation.pdf »…). Chaque fichier est
 * rapproché des rubriques déjà présentes sur le dossier : si la rubrique existe,
 * le fichier est remplacé ; sinon il est ajouté comme nouvelle rubrique. Les
 * rubriques non concernées restent intactes.
 */
class UpdateDossier extends Command
{
    protected $signature = 'dossier:update
                            {code : Code du dossier (ex. CAPE-5JR63X)}
                            {--coords= : Coordonnées "lat,lng" à enregistrer}
                            {--files= : Dossier source contenant les fichiers à intégrer}
                            {--force : Applique réellement (sans cette option, simple simulation)}';

    protected $description = "Met à jour les coordonnées et/ou les fichiers d'un dossier";

    public function handle()
    {
        $requete = Requete::where('code', $this->argument('code'))->first();

        if (! $requete) {
            $this->error('Dossier introuvable : '.$this->argument('code'));

            return 1;
        }

        $this->info("Dossier : {$requete->code} — {$requete->name}");
        $force = $this->option('force');

        // ── Coordonnées ──────────────────────────────────────────────────────
        $coords = $this->option('coords');
        if ($coords !== null) {
            $this->line("  Coordonnées : « {$requete->coords} » → « {$coords} »");
        }

        // ── Fichiers ─────────────────────────────────────────────────────────
        $plan = [];
        if ($source = $this->option('files')) {
            $source = rtrim($source, '/');
            if (! is_dir($source)) {
                $this->error("Dossier source introuvable : $source");

                return 1;
            }

            // Rubriques déjà présentes sur le dossier (référence => id fichier).
            $existing = $requete->files()->get()
                ->merge($requete->files2()->get())
                ->pluck('id', 'reference')->all();

            foreach (glob($source.'/*') as $path) {
                if (! is_file($path)) {
                    continue;
                }

                $label = pathinfo($path, PATHINFO_FILENAME);
                $matchId = TextMatcher::bestMatch($label, $existing);

                $plan[] = [
                    'path' => $path,
                    'label' => $label,
                    'reference' => $matchId ? array_search($matchId, $existing, true) : $label,
                    'file_id' => $matchId,
                    'action' => $matchId ? 'remplace' : 'ajoute',
                ];
            }

            $this->newLine();
            $this->table(
                ['Fichier source', 'Action', 'Rubrique'],
                array_map(fn ($p) => [
                    basename($p['path']),
                    $p['action'],
                    mb_strimwidth($p['reference'], 0, 45, '…'),
                ], $plan)
            );
        }

        if ($coords === null && $plan === []) {
            $this->warn('Rien à faire : précisez --coords et/ou --files.');

            return 0;
        }

        if (! $force) {
            $this->newLine();
            $this->info('Simulation — relancez avec --force pour appliquer.');

            return 0;
        }

        DB::transaction(function () use ($requete, $coords, $plan) {
            if ($coords !== null) {
                $requete->update(['coords' => $coords]);
            }

            foreach ($plan as $p) {
                $filename = $this->storeFile($requete->code, $p['path']);

                if ($p['file_id']) {
                    RequeteFile::where('id', $p['file_id'])->update(['filename' => $filename]);
                } else {
                    RequeteFile::create([
                        'type' => strtoupper(pathinfo($p['path'], PATHINFO_EXTENSION)),
                        'reference' => $p['reference'],
                        'filename' => $filename,
                        'level' => 0,
                        'file_id' => null,
                        'requete_id' => $requete->id,
                    ]);
                }
            }
        });

        $this->info('Mise à jour effectuée.');

        return 0;
    }

    /**
     * Copie le fichier dans public/docs/{code}/ sous un nom sûr (sans accent ni
     * espace) et renvoie ce nom, tel que stocké dans requete_files.
     */
    private function storeFile(string $code, string $path): string
    {
        $dir = public_path('docs/'.$code);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $filename = Str::slug(pathinfo($path, PATHINFO_FILENAME)).'-'.Str::random(6).'.'.$ext;

        copy($path, $dir.'/'.$filename);

        return $filename;
    }
}
