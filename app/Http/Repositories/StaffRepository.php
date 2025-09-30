<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Staff;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class StaffRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Staff
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Staff::class);
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

        $req = Staff::where('cape_id', Auth::user()->cape_id)
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
    public function makeStore($data): Staff
    {
        $datas = $request->all();
        $datas['birthdate'] = date_create($datas['birthdate']);
        $datas['cape_id'] = Auth::user()->cape->id;

        $staff = new Staff($datas);
        $staff->save();

        return $staff;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Staff
    {
        $datas = $request->all();
        $staff = Staff::findOrFail($id);
        $datas['birthdate'] = date_create($datas['birthdate']);
        $staff->update($datas);
        return $staff;
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
        $staff = Staff::findOrFail($id);
        $staff->update(['is_active' => $status]);
        return $staff;
    }


    public function show($id)
    {
        $staff = Staff::findOrFail($id);
        return $staff;
    }

}
