<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\ActivityReport;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ActivityReportRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var ActivityReport
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(ActivityReport::class);
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
        $service_id = $request->service_id ?? null;
        $type = $request->type ?? null; // récupérer le type depuis la requête

        switch ($type) {
            case 'cape-inscrits':
                $req = $this->getForCape($service_id)
                    ->ignoreRequest(['per_page'])
                    ->filter(array_filter($request->all(), function ($k) {
                        return $k != 'page';
                    }, ARRAY_FILTER_USE_KEY))
                    ->orderByDesc('created_at');
                break;

            case 'cape-autorises':
                $req = $this->getForCapeAuthorized($service_id)
                    ->ignoreRequest(['per_page'])
                    ->filter(array_filter($request->all(), function ($k) {
                        return $k != 'page';
                    }, ARRAY_FILTER_USE_KEY))
                    ->orderByDesc('created_at');
                break;

            default:
                return collect(); // retourne une collection vide
        }

        // Pagination si demandée
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request->per_page;
            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    function getForCape($service_id) 
    {
        $requetes = Requete::with('district.municipality.department')
            ->where('service_id', $service_id)
            ->get();

        return $requetes;
    }


    function getForCapeAuthorized($service_id) 
    {
        $requetes = Requete::with('district.municipality.department')
            ->where('is_authorized', true)
            ->where('has_agreemant', true)
            ->where('service_id', $service_id)
            ->get();

        return $requetes;
    }

    /**
     * Récupère une fête spécifique.
     */
    public function get($id)
    {
        return $this->findOrFail($id);
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

}
