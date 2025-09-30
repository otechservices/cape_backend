<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeGarderie;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeGarderieRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeGarderie
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeGarderie::class);
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

        $req = TypeGarderie::ignoreRequest(['per_page'])
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


    function getForCape() 
    {
        $activity_report=TypeGarderie::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();
        return $activity_report;
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
    public function makeStore(Request $request): TypeGarderie
    {
        $datas = $request->all();
        $type_garderies = new TypeGarderie($datas);
        $type_garderies->save();
        return $type_garderies;

    }

    /**
     * Met à jour une fête.
     */
    public function update(Request $request, $id)
    {
        $type_garderie = TypeGarderie::find($id);
        $type_garderie->update($request->all());
        return TypeGarderie::find($id);
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
        $type_garderie = TypeGarderie::find($id);
        $type_garderie->update(['is_active' => $status]);
        return $type_garderie;
    }


    public function show($id)
    {
        return TypeGarderie::find($id);
    }


}
