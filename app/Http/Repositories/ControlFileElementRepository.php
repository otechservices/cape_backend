<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\ControlFileElement;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ControlFileElementRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var ControlFileElement
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(ControlFileElement::class);
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

        $req = ControlFileElement::with('service')
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
    public function makeStore(Request $request): ControlFileElement
    {
        $datas = $request->all();

        $dataR = new ControlFileElement($datas);
        $dataR->save();

        return $dataR;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): ControlFileElement
    { 
        $datas = $request->all();
        $dataR = ControlFileElement::findOrFail($id);
        $dataR->update($datas);

        return $dataR;
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
        $dataR = ControlFileElement::findOrFail($id);
        $dataR->update(['is_active' => $status]);

        return $dataR;
    }

    public function getAllActives($serviceId)
    {
        $dataR = ControlFileElement::where('is_active', true)
            ->where('service_id', $serviceId)
            ->get();

        return $dataR;
    }

    public function show($id)
    {
        $dataR = ControlFileElement::findOrFail($id);
        return $dataR;
    }

}
