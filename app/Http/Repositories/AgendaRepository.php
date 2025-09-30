<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
use App\Models\Agenda;


class AgendaRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Agenda
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Agenda::class);
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

        $req = Agenda::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $agenda = $req->paginate($per_page);
        } else {
            $agenda = $req->get();
        }

        return $agenda;


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
    public function makeStore(Request $request): Agenda
    {
        $datas = $request->all();

        $agenda = new Agenda($datas);
        $agenda->save();

        return $agenda;

    }

    /**
     * Met à jour une fête.
     */
   public function update(Request $request, $id)
    {
        $datas = $request->all();

        $agenda = Agenda::findOrFail($id);
        $agenda->update($datas);

        return $agenda;
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
        $agenda = Agenda::findOrFail($id);
        $agenda->update(['is_active' => $status]);

        return $agenda;
    }

    public function getDepartmentWithRelation()
    {
        $departments = Agenda::with(['Municipalities.districts'])->get();
        return $departments;
    }


}
