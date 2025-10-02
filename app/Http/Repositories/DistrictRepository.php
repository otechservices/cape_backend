<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\District;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class DistrictRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var District
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(District::class);
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

        $req = District::with('Municipality')
            ->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $districts = $req->paginate($per_page);
        } else {
            $districts = $req->get();
        }

        return $districts;
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
    public function makeStore(Request $request): District
    {
        $datas = $request->all();

        $district = new District($datas);
        $district->save();

        return $district;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): District
    {
        $datas = $request->all();

        $district = District::findOrFail($id);
        $district->update($datas);

        return $district;
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
        $district = District::findOrFail($id);
        $district->update(['is_active' => $status]);

        return $district;
    }

}
