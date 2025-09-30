<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Municipality;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class MunicipalityRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Municipality
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Municipality::class);
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

        // Construire la requête avec relation et filtres
        $req = Municipality::with('Department')
            ->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Pagination ou récupération brute
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request->per_page;
            $municipalities = $req->paginate($per_page);
        } else {
            $municipalities = $req->get();
        }

        return $municipalities;

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
    public function makeStore(Request $request): Municipality
    {
        $datas = $request->all();
        $municipality = new Municipality($datas);
        $municipality->save();

        return $municipality;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Municipality
    {
        $datas = $request->all();

        $municipality = Municipality::findOrFail($id);
        $municipality->update($datas);

        return $municipality;
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
        $municipality = Municipality::find($id);

        if ($municipality) {
            $municipality->update(['is_active' => $status]);
            return $municipality; 
            }
            return null;
    }

    
}
