<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Info;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class InfoRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Info
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Info::class);
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

        // Construire la requête de base avec relation et filtres
        $req = Info::with('TypeInfo')
            ->ignoreRequest(['per_page'])
            ->filter(array_filter(request()->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Pagination ou récupération totale
        if (array_key_exists('per_page', request()->all())) {
            $per_page = request()->per_page;
            $info = $req->paginate($per_page);
        } else {
            $info = $req->get();
        }

        return $info;

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
    public function makeStore(Request $request): Info
    {
        $datas = $request->all();
        $info = new Info($datas);
        $info->save();

        return $info;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Info
    {
       $datas = $request->all();
        $info = Info::find($id);

        if ($info) {
            $info->update($datas);
            return $info;
        }

        return null;
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

    public function setStatus($id, $status)
    {
        $info = Info::find($id);

        if ($info) {
            $info->update([
                'has_answer' => $status,
                'user_id' => Auth::id()
            ]);
            return $info; 
        }

        return null; 
    }

    public function getDepartmentWithRelation()
    {
        $info = Info::with(['Municipalities.districts'])->get();

        return $info;
    }

}
