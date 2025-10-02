<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeAvis;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeAvisRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeAvis
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeAvis::class);
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

        $req = TypeAvis::ignoreRequest(['per_page'])
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
    

    public function show($id)
    {
        $model = TypeAvis::findOrFail($id);
        return $model;
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
    public function makeStore($data): TypeAvis
    {
        $data = $request->all();
        $model = new TypeAvis($data);
        $model->save();

        return $model;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): TypeAvis
    {
       
        $data = $request->all();
        $model = TypeAvis::findOrFail($id);
        $model->update($data);

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
        $model = TypeAvis::findOrFail($id);
        $model->update(['is_active' => $status]);

        return $model;
    }


    public function getDepartmentWithRelation()
    {
        $models = TypeAvis::with(['Municipalities.districts'])->get();
        return $models;
    }


}
