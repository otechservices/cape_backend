<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Actuality;
 
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;
use Str;

class ActualityRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Actuality
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Actuality::class);
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

        $req = Actuality::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $actuality = $req->paginate($per_page);
        } else {
            $actuality = $req->get();
        }

        return $actuality;

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
    public function makeStore(Request $request): Actuality
    {
        $datas = $request->all();

        if ($request->file('big_photo')) {
            $filename = FileStorage::setFile(
                'public',
                $request->file('big_photo'),
                'actualities',
                Str::slug($request->title) . time()
            );
            $datas['big_photo'] = $filename;
        }

        if ($request->file('short_photo')) {
            $filename = FileStorage::setFile(
                'public',
                $request->file('short_photo'),
                'actualities',
                Str::slug($request->title) . time()
            );
            $datas['short_photo'] = $filename;
        }

        $datas['user_id'] = Auth::id();

        $actuality = new Actuality($datas);
        $actuality->save();

        return $actuality;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Actuality
    {
       
        $datas = $request->all();

        $actuality = Actuality::findOrFail($id);

        if ($request->file('big_photo')) {
            FileStorage::deleteFile('public', $actuality->big_photo, 'actualities');
            $filename = FileStorage::setFile(
                'public',
                $request->file('big_photo'),
                'actualities',
                Str::slug($request->title) . time()
            );
            $datas['big_photo'] = $filename;
        }

        if ($request->file('short_photo')) {
            FileStorage::deleteFile('public', $actuality->short_photo, 'actualities');
            $filename = FileStorage::setFile(
                'public',
                $request->file('short_photo'),
                'actualities',
                Str::slug($request->title) . time()
            );
            $datas['short_photo'] = $filename;
        }

        $actuality->update($datas);

        return $actuality;

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
    public function setPublishState($id, $state)
    {
        $actuality = Actuality::findOrFail($id);

        $actuality->update(['is_active' => $state]);

        return $actuality;

    }


    public function show()
    {
        $actuality = Actuality::findOrFail($id);

        return $actuality;
}

}
