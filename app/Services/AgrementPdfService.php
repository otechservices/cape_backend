<?php

namespace App\Services;

use App\Models\Requete;
use App\Models\RequeteFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

/**
 * Agrément PDF délivré par la plateforme, partagé par les parcours qui
 * reconnaissent un agrément hors session (revendication d'un centre importé,
 * déclaration directe par la DFEA).
 */
class AgrementPdfService
{
    /**
     * Produit l'agrément PDF et l'attache au dossier.
     *
     * La décision de la DFEA est déjà actée en base au moment où l'on passe ici :
     * un incident d'écriture ne doit donc pas la faire disparaître. En cas
     * d'échec, on renonce au PDF — et à la ligne `requete_files` qui pointerait
     * sur un fichier absent — plutôt qu'à l'agrément lui-même.
     *
     * @return string|null Chemin du PDF produit, null si la génération a échoué.
     */
    public static function generate(Requete $requete): ?string
    {
        $recFile = time().'agrement.pdf';
        $directory = public_path('docs/'.$requete->code);
        $filePath = $directory.'/'.$recFile;

        try {
            // Un centre importé n'a pu créer son répertoire qu'en déposant ses
            // pièces ; on ne présume pas qu'il existe.
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            Pdf::loadView('emails.agrement_pj', ['name' => $requete->name])->save($filePath);

            RequeteFile::create([
                'type' => 'PDF',
                'reference' => "Agrément d'autorisation",
                'filename' => $recFile,
                'level' => 1,
                'file_id' => null,
                'requete_id' => $requete->id,
            ]);

            return $filePath;
        } catch (\Throwable $th) {
            Log::error("Agrément PDF non généré pour {$requete->code} : ".$th->getMessage());

            return null;
        }
    }
}
