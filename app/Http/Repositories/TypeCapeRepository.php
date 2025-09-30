<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeCape;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeCapeRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeCape
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeCape::class);
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

        $req = TypeCape::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
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
    public function makeStore(Request $request): TypeCape
    {
        $datas = $request->all();
        $model = new TypeCape($datas);
        $model->save();
        return $model;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): TypeCape
    {
        $datas = $request->all();
        $model = TypeCape::findOrFail($id);
        $model->update($datas);
        return $model;
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
        $model = TypeCape::findOrFail($id);
        $model->update(['is_active' => $status]);
        return $model;
    }


    public function getDepartmentWithRelation()
    {
        $models = TypeCape::with(['Municipalities'])->get();
        return $models;
    }


    public function show($id)
    {
        $model = TypeCape::findOrFail($id);
        return $model;
    }


}
