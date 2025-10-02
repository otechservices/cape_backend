<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\UniteAdmin;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class UniteAdminRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var UniteAdmin
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(UniteAdmin::class);
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

        $req = UniteAdmin::ignoreRequest(['per_page'])
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
    public function makeStore(Request $request): UniteAdmin
    {
        $datas = $request->all();
        $ua = new UniteAdmin($datas);
        $ua->save();

        return $ua;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): UniteAdmin
    {
        $ua = UniteAdmin::find($id);
        $ua->update($request->all());
        return UniteAdmin::find($id);
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
        $ua = UniteAdmin::find($id);
        $ua->update(['is_active' => $status]);
        return $ua;
    }


    public function getDepartmentWithRelation()
    {
        $models = UniteAdmin::with(['Municipalities'])->get();
        return $models;
    }


    public function show($id)
    {
        return UniteAdmin::find($id);
    }

}
