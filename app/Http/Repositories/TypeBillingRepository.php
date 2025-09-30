<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeBilling;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeBillingRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeBilling
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeBilling::class);
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

        $req = TypeBilling::ignoreRequest(['per_page'])
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
        $activity_report=TypeBilling::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();
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
    public function makeStore($data): TypeBilling
    {
        $datas = $request->all();

        $model = new TypeBilling($datas);
        $model->save();
        return $model;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): TypeBilling
    {
        $datas = $request->all();

        $model = TypeBilling::findOrFail($id);
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
        $model = TypeBilling::findOrFail($id);
        $model->update(['is_active' => $status]);
        return $model;
    }


    public function show($id)
    {
        $model = TypeBilling::findOrFail($id);
        return $model;
    }


    public function getDepartmentWithRelation()
    {
        $models = TypeBilling::with(['Municipalities.districts'])->get();
        return $models;
    }   

}
