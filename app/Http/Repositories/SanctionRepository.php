<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Sanction;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class SanctionRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Sanction
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Sanction::class);
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

        $req = Sanction::with(['cape.requete', 'TypeSanction'])
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


    function getForCape() 
    {
        $activity_report=Sanction::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();
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
    public function makeStore(Request $request): Sanction
    {
        $datas = $request->all();
        if ($request->file('file')) {
            $datas["decret_filename"] = FileStorage::setFile(
                "doc_store",
                $request->file('file'),
                Cape::find($request->cape_id)->requete->code,
                 time()
            );
            unset($datas['file']);
        }

        $section = new Sanction($datas);
        $section->save();
        return $section;
    }

    public function show($id)
    {
        $section = Sanction::find($id);
        return $section;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request,$id): Sanction
    {
        $datas = $request->all();

        if ($request->file('file')) {
            $datas["decret_filename"] = FileStorage::setFile(
                "doc_store",
                $request->file('file'),
                Cape::find($request->cape_id)->requete->code,
                time()
            );
            unset($datas['file']);
        }

        $section = Sanction::findOrFail($id);
        $section->update($datas);
        return $section;
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
        $section = Sanction::findOrFail($id);
        $section->update(['status' => $status]);
        return $section;
    }

}
