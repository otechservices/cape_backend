<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\File;
use App\Models\Cape;
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
        $type = request()->type;
        $per_page = 10;

        $checkService = Service::where('name', 'like', '%' . $type . '%')->first();

        if ($checkService) {
            $req = File::with("TypeFile")
                ->where('service_id', $checkService->id)
                ->where('is_published', true)
                ->ignoreRequest(['per_page'])
                ->filter(array_filter(request()->all(), function ($k) {
                    return $k != 'page';
                }, ARRAY_FILTER_USE_KEY))
                ->orderByDesc('created_at');
        } else {
            $req = File::with(["TypeFile", 'type'])
                ->ignoreRequest(['per_page'])
                ->filter(array_filter(request()->all(), function ($k) {
                    return $k != 'page';
                }, ARRAY_FILTER_USE_KEY))
                ->orderByDesc('created_at');
        }

        // Pagination
        if (array_key_exists('per_page', request()->all())) {
            $per_page = request()->per_page;
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
