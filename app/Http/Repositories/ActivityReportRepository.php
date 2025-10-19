<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\ActivityReport;
use App\Models\Requete;
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

        // Commencer la requête de base avec les relations et filtres
        $req = ActivityReport::with(['responses', 'centre.TypeCape'])
            ->ignoreRequest(['per_page','service_id']) // on ignore per_page
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
                $req = $req->where('promoter_id', Auth::user()->promoter_id);
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
        $activity_report=ActivityReport::with(["centre","responses"])->where('promoter_id',Auth::user()->promoter_id)->get();
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
    public function makeStore($data): ActivityReport
    {
        $code=Requete::find($data['centre_id'])?->code;
        $data["activity_report_filename"]= FileStorage::setFile("doc_store",request()->file('activity_report_filename'),$code."/reports",time()."-rapport_activité");
        $data["financial_report_filename"]= FileStorage::setFile("doc_store",request()->file('financial_report_filename'),$code."/reports",time()."-rapport_financier");
        $data['promoter_id']=Auth::user()->promoter_id;
        $activity_report=ActivityReport::create($data);

        return $activity_report;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): ActivityReport
    {
       
        $activity_report=ActivityReport::find($id);
      if(request()->file('activity_report_filename'))  $data["activity_report_filename"]= FileStorage::setFile("doc_store",request()->file('activity_report_filename'),$code."/reports",time()."-rapport_activité");
        if(request()->file('financial_report_filename'))$data["financial_report_filename"]= FileStorage::setFile("doc_store",request()->file('financial_report_filename'),$code."/reports",time()."-rapport_financier");

        $activity_report->update($data);

        $activity_report=ActivityReport::find($id);

        return $activity_report;
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
         $data=[];
        $activity_report=ActivityReport::find($id);
        $data['status']=$status;
        if($status==2) $data['is_transmitted']=false;
        $activity_report->update($data);
        return $activity_report;
    }

    /**
     * Recherche dans les fêtes (par nom, lieu...).
     */
    public function search($term)
    {
        $query = ActivityReport::query();
        $attrs = ['nom', 'lieu', 'type_ActivityReport'];
        
        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get();
    }

    public function send($id) 
    {
        $activity_report=ActivityReport::find($id);
            $activity_report->update(['is_transmitted' =>true]);
            return $activity_report;
    }


    public function getDepartmentWithRelation()
    {
        $departments=ActivityReport::with(['Municipalities'])->get();
        return $departments;
    }


    public function storeDistricts(Request $request)
    {

        foreach (json_decode($request->items) as $value) {
           District::find($value->id)->update(['cps_id'=>$request->id]);
        }

        return true;
       
    }


    public function exportPDF()
    {
        $data=ActivityReport::all();

        $pdf=Pdf::loadView('pdf.activity_report', [
            "datas"=>$data,
        ]);

        return $pdf->download('liste_cps.pdf');
    }

}
