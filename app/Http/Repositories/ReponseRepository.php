<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Reponse;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ReponseRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Reponse
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Reponse::class);
    }

    /**
     * Vérifie si la fête existe.
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Récupère toutes les fêtes avec pagination et filtres.
     */
    public function getAll($request)
    {

    }

    /**
     * Récupère une fête spécifique.
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Supprime une fête.
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Récupère les fêtes les plus récentes.
     */
    public function getlatest()
    {
        return $this->latest()->get();
    }

    public function needCorrection(Request $request)
    {
        $req = Requete::whereId($request->id)->first();
        $reponse = new Reponse([
            'observation' => $request->observation,
            'requete_id'  => $request->id,
            'user_id'     => Auth::id(),
        ]);
        $reponse->save();

        $parcours = new Parcours([
            'libelle'    => "Demande mise en attente pour complément d'information. Motif: " . $request->observation,
            'requete_id' => $request->id,
            'user_id'    => Auth::id(),
        ]);
        $parcours->save();

        $token = Str::random(60);

        Mailer::sendSimple(
            "emails.update",
            [
                'code'  => $req->code,
                'token' => $token,
                'motif' => $request->observation,
            ],
            "Avis sur demande d'autorisation " . $req->service?->name,
            $req->name_promoter,
            $req->email
        );

        $req->update([
            "status" => 1,
            "token"  => $token,
        ]);

        return $reponse;
    }

    /**
     * Recherche dans les fêtes (par nom, lieu...).
     */
    public function decline(Request $request)
    {
        $req = Requete::whereId($request->id)->first();
        $reponse = new Reponse([
            'hasPermission' => $request->hasPermission,
            'reason'        => $request->reason,
            'observation'   => $request->observation,
            'requete_id'    => $request->id,
            'user_id'       => Auth::id(),
        ]);
        $reponse->save();

        $parcours = new Parcours([
            'libelle'    => "Demande rejetée",
            'requete_id' => $request->id,
            'user_id'    => Auth::id(),
        ]);
        $parcours->save();

        Mailer::sendSimple(
            "emails.rejected",
            [
                'motif' => $request->observation
            ],
            "Avis sur demande d'autorisation " . $req->service?->name,
            $req->name_promoter,
            $req->email
        );

        $req->update([
            "status" => 2,
        ]);

        return $reponse;
    }


    public function validation(Request $request) 
    {
        $req = Requete::whereId($request->id)->first();
        $reponse = new Reponse([
            'hasPermission' => $request->hasPermission,
            'reason'        => $request->reason,
            'observation'   => $request->observation,
            'requete_id'    => $request->id,
            'user_id'       => Auth::id(),
        ]);
        $reponse->save();

        $parcours = new Parcours([
            'libelle'    => "Demande rejetée",
            'requete_id' => $request->id,
            'user_id'    => Auth::id(),
        ]);
        $parcours->save();

        Mailer::sendSimple(
            "emails.success",
            [
                'code'  => $req->code,
                'motif' => $request->observation,
            ],
            "Validation demande d'autorisation " . $req->service?->name,
            $req->name_promoter,
            $req->email
        );

        $req->update([
            "status" => 4,
        ]);

        return $reponse;
    }

}
