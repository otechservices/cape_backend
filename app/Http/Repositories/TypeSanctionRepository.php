<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeSanction;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeSanctionRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeSanction
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeSanction::class);
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

        $req = TypeSanction::ignoreRequest(['per_page'])
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
    public function makeStore(Request $request): TypeSanction
    {
        $datas = $request->all();
        $type_sanction = new TypeSanction($datas);
        $type_sanction->save();

        return $type_sanction;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): TypeSanction
    {
        $datas = $request->all();
        $model = TypeSanction::findOrFail($id);
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
        $type_sanction = TypeSanction::find($id);
        $type_sanction->update(['is_active' => $status]);
        return $type_sanction;
    }


    public function getDepartmentWithRelation()
    {
        return TypeSanction::with(['Municipalities.districts'])->get();
    }


    public function show($id)
    {
        return TypeSanction::find($id);
    }


}
