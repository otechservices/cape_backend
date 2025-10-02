<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeSousGarderie;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeSousGarderieRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeSousGarderie
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeSousGarderie::class);
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

        $req = TypeSousGarderie::with('TypeGarderie')
            ->ignoreRequest(['per_page'])
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
    public function makeStore(Request $request): TypeSousGarderie
    {
        $datas = $request->all();

        $type_sous_garderies = new TypeSousGarderie($datas);
        $type_sous_garderies->save();

        return $type_sous_garderies;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): TypeSousGarderie
    {
        $type_sous_garderie = TypeSousGarderie::find($id);
        $type_sous_garderie->update($request->all());
        return TypeSousGarderie::find($id);
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
        $type_sous_garderie = TypeSousGarderie::find($id);
        $type_sous_garderie->update(['is_active' => $status]);
        return $type_sous_garderie;
    }


    public function getDepartmentWithRelation()
    {
        $models = TypeSousGarderie::with(['Municipalities'])->get();
        return $models;
    }


    public function show($id)
    {
        return TypeSousGarderie::find($id);
    }

}
