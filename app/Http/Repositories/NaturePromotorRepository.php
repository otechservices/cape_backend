<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\NaturePromotor;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class NaturePromotorRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var NaturePromotor
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(NaturePromotor::class);
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

        // Construire la requête avec filtres et tri
        $req = NaturePromotor::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Pagination ou récupération brute
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request->per_page;
            $nature_promotors = $req->paginate($per_page);
        } else {
            $nature_promotors = $req->get();
        }

        return $nature_promotors;
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
    public function makeStore(Request $request): NaturePromotor
    {
        $datas = $request->all();

        $nature_promotor = new NaturePromotor($datas);
        $nature_promotor->save();

        return $nature_promotor;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): NaturePromotor
    {
       $datas = $request->all();

        $nature_promotor = NaturePromotor::findOrFail($id);
        $nature_promotor->update($datas);

        return $nature_promotor;
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

    public function setStatus($id, $status)
    {
        $nature_promotor = NaturePromotor::find($id);

        if ($nature_promotor) {
            $nature_promotor->update(['is_active' => $status]);
            return $nature_promotor; 
        }
        return null;
    }

    public function show($id)
    {
        $nature_promotor = NaturePromotor::find($id);
        return $nature_promotor;
    }

}
