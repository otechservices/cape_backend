<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\TypeFile;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class TypeFileRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var TypeFile
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(TypeFile::class);
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

        $req = TypeFile::ignoreRequest(['per_page'])
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
        $activity_report=TypeFile::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();
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
    public function makeStore(Request $request): TypeFile
    {
        $datas = $request->all();
        $type_file = new TypeFile($datas);
        $type_file->save();
        return $type_file;

    }

    /**
     * Met à jour une fête.
     */
    public function update(Request $request, $id)
    {
        $type_file = TypeFile::find($id);
        $type_file->update($request->all());
        return TypeFile::find($id);
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
        $type_file = TypeFile::find($id);
        $type_file->update(['is_active' => $status]);
        return $type_file;
    }


    public function show($id)
    {
        return TypeFile::find($id);
    }


    public function getDepartmentWithRelation()
    {
        return TypeFile::with(['Municipalities.districts'])->get();
    }


}
