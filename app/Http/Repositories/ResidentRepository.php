<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Resident;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ResidentRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Resident
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Resident::class);
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
        $per_page = 10;

        // Construction de la requête avec filtrage et tri
        $req = Resident::where('cape_id', Auth::user()->cape->id)
            ->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Gestion de la pagination
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $residents = $req->paginate($per_page);
        } else {
            $residents = $req->get();
        }

        // Retour des données directement (pas de JSON)
        return $residents;
    }

    /**
     * Récupère une fête spécifique.
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Crée une nouvelle fête.
     */
    public function makeStore(Request $request): Resident
    {
        $datas = $request->all();
        $datas['birthdate'] = date_create($datas['birthdate']);
        $datas['cape_id'] = Auth::user()->cape->id;

        // Création du résident
        $resident = new Resident($datas);
        $resident->save();

        // Retour direct du modèle
        return $resident;
    }
    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Resident
    {
        $datas = $request->all();
        $datas['birthdate'] = date_create($datas['birthdate']);
        $resident = Resident::findOrFail($id);
        $resident->update($datas);
        return $resident;
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

    /**
     * Modifie le statut d'une fête.
     */
    public function setStatus($id, $status)
    {
        $resident = Resident::findOrFail($id);
        $resident->update(['is_active' => $status]);
        return $resident;
    }

}
