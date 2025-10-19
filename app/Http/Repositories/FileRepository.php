<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\File;
use App\Models\Cape;
use App\Models\Service;
use App\Utilities\FileStorage;


use Auth;

class FileRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var File
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(File::class);
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
        $type = request()->input('type');
        $perPage = request()->input('per_page', 10);

        $checkService = null;
        if (!empty($type)) {
            $checkService = Service::where('name', 'like', "%{$type}%")->first();
        }

        // Base query
        $req = File::with(['TypeFile', 'type'])
            ->when($checkService, function ($q) use ($checkService) {
                $q->where('service_id', $checkService->id);
            })
            // Appliquer is_active si présent
            ->when(request()->filled('is_active'), function ($q) {
                $q->where('is_active', request()->boolean('is_active'));
            })
            // Appliquer is_published si présent (si tu veux garder ce filtre optionnel)
            ->when(request()->filled('is_published'), function ($q) {
                $q->where('is_published', request()->boolean('is_published'));
            })
            ->ignoreRequest(['per_page', 'type'])
            ->orderByDesc('created_at');

        // Pagination ou collection complète
        return request()->has('per_page') ? $req->paginate($perPage) : $req->get();



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
    public function makeStore(Request $request): File
    {
        $datas = $request->all();
        $file = File::create($datas);

        return $file;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): File
    {
       $datas = $request->all();
        $file = File::find($id);

        if ($file) {
            $file->update($datas);
            
        return $file;
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


    public function check($token)
    {
        $check = RequeteFile::with(['devliver', 'requete'])
                            ->where('token', $token)
                            ->first();

        if ($check) {
            // Document trouvé, on retourne le modèle
            return $check;
        } 

        // Document non trouvé
        return null;
    }
  
}
