<?php

namespace App\Traits;

use App\Models\Requete;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Réserve les actions de traitement au détenteur actuel du dossier.
 *
 * Tout agent peut ouvrir un dossier depuis « Parcours traitement », mais seul
 * celui qui le détient peut le faire avancer. Masquer les boutons côté front
 * ne suffisait pas : l'API restait appelable directement.
 */
trait GuardsRequeteHolder
{
    /**
     * Réponse de refus à renvoyer telle quelle, ou null si l'action est permise.
     */
    protected function denyUnlessHolder(?Requete $requete): ?JsonResponse
    {
        if ($requete === null) {
            return response()->json([
                "success" => false,
                "message" => "Dossier introuvable",
                "data" => null
            ], 404);
        }

        if (! $requete->isHeldBy(Auth::user())) {
            return response()->json([
                "success" => false,
                "message" => "Ce dossier n'est pas entre vos mains : il n'est accessible qu'en consultation.",
                "data" => null
            ], 403);
        }

        return null;
    }
}
