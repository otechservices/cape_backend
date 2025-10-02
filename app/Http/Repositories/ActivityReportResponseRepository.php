<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\ActivityReportResponse;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ActivityReportResponseRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var ActivityReportResponse
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(ActivityReportResponse::class);
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

        // Commencer la requête de base avec les relations et filtres
        $req = ActivityReportResponse::with(['responses', 'requete.TypeCape'])
            ->ignoreRequest(['per_page']) // on ignore per_page
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Récupérer le rôle de l'utilisateur
        $role = Auth::user()->roles()->first()->name;

        // Adapter la requête selon le rôle
        switch ($role) {
            case 'ministre':
            case 'dfea':
                if ($request->service_id) {
                    $service_id = $request->service_id;
                    $req->whereHas('requete', function ($q) use ($service_id) {
                        $q->where('service_id', $service_id);
                    })->where('is_transmitted', true);
                }
                break;

            case 'ddasm':
                $districtIds = [];
                foreach (Auth::user()->department->municipalities as $municipality) {
                    foreach ($municipality->districts as $district) {
                        $districtIds[] = $district->id;
                    }
                }
                if ($request->service_id) {
                    $service_id = $request->service_id;
                    $req->whereHas('requete', function ($q) use ($districtIds, $service_id) {
                        $q->whereIn('district_id', $districtIds)
                        ->where('service_id', $service_id);
                    })->where('is_transmitted', true);
                }
                break;

            case 'cps':
                $districtIds = Auth::user()->cps->districts->pluck('id');
                if ($request->service_id) {
                    $service_id = $request->service_id;
                    $req->withCount(['residents', 'staffs'])
                        ->whereHas('requete', function ($q) use ($districtIds, $service_id) {
                            $q->whereIn('district_id', $districtIds)
                            ->where('service_id', $service_id);
                        })->where('is_transmitted', true);
                }
                break;

            case 'cape':
                $req = $req->where('cape_id', Auth::user()->cape_id);
                break;

            default:
                $req = $req->whereRaw('1=0'); // retourne rien pour les autres rôles
                break;
        }

        // Pagination
        if ($request->has('per_page')) {
            $per_page = $request->per_page;
            $activity_report = $req->paginate($per_page);
        } else {
            $activity_report = $req->get();
        }

        return $activity_report;

    }


    function getForCape() 
    {
         $activity_report=ActivityReportResponse::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();
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
    public function makeStore($data): ActivityReportResponse
    {
         $datas = $request->all();
        
        $activity_report_response=ActivityReportResponse::create($datas);

        return $activity_report_response;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): ActivityReportResponse
    {
       
        $datas = $request->all();

        $activity_report_response = ActivityReportResponse::findOrFail($id);
        $activity_report_response->update($datas);

        return $activity_report_response;
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
        $activity_report_response=ActivityReportResponse::find($id);
        $activity_report_response->update(['is_active' =>$status]);

        return $activity_report_response;

    }

    public function show($id) 
    {
        $activity_report_response=ActivityReportResponse::find($id);
    
        return $activity_report_response;
    }


    
}
