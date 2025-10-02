<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeControl;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeControlRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeControl
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeControl::class);
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

        $req = TypeControl::ignoreRequest(['per_page'])
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
        $activity_report=TypeControl::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();
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
    public function makeStore(Request $request): TypeControl
    {
        $datas = $request->all();
        $type_control = new TypeControl($datas);
        $type_control->save();
        return $type_control;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): TypeControl
    {
        $type_control = TypeControl::find($id);
        $type_control->update($request->all());
        return TypeControl::find($id);
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
        $type_control = TypeControl::find($id);
        $type_control->update(['is_active' => $status]);
        return $type_control; // ou null
    }


    public function getDepartmentWithRelation()
    {
        return TypeControl::with(['Municipalities.districts'])->get();
    }


    public function show($id)
    {
        return TypeControl::find($id);
    }

}
