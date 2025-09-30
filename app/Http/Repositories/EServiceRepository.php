<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\EService;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class EServiceRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var EService
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(EService::class);
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
        $req = EService::with(['responses', 'requete.TypeCape'])
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
        $activity_report=EService::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();
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
    public function makeStore(Request $request): EService
    {
        $code = $request->code;

        if ($code == null) {
            // Création d'un nouvel agent
            $model = new Agent($request->all());
            $model->save();
        } else {
            // Mise à jour de l'agent existant
            $model = Agent::find($code);
            if ($model) {
                $model->update($request->all());
            } else {
                // Si l'agent n'existe pas, on peut créer un nouvel agent ou gérer autrement
                $model = new Agent($request->all());
                $model->save();
            }
        }

        return $model;
    }

    /**
     * Récupère les fêtes les plus récentes.
     */
    public function getlatest()
    {
        return $this->latest()->get();
    }


    function getOne($token, $code)
    {
        $check = Requete::whereToken($token)->whereCode($code)->first();

        if ($check) {
            $requetes = Requete::with([
                'files.file.TypeFile',
                'reponses',
                'parcours',
                'affectation',
                'TypeCape',
                'service',
                'RequeteTypeGarderies.TypeGarderie',
                'district.Municipality.Department'
            ])->where('code', $code)->first();

            return $requetes; // retourne directement le modèle
        } else {
            return null; // ressource non trouvée
        }
    }


    public static function sendSubmittedMail($name, $email, $subject, $data, $file)
    {
        Mail::send($file, $data, function ($message) use ($name, $email, $subject) {
            $message->from(env('MAIL_FROM_ADDRESS'), env("MAIL_FROM_NAME"))
                    ->subject($subject)
                    ->to($email, $name);
        });

        return true; // on peut juste retourner true
    }


    function addFile(Request $request)
    {
        $filename = FileStorage::setFile("doc_store", $request->file, $request->init_code, $request->name . Str::random());

        RequeteFile::create([
            "type" => "PDF",
            "reference" => $request->reference,
            "filename" => $filename,
            "level" => 0,
            "file_id" => $request->file_id,
            "init_code" => $request->init_code,
        ]);

        return true; // succès
    }


    function purgeFile(Request $request)
    {
        $files = RequeteFile::where('init_code', $request->init_code)
                            ->where('requete_id', null)
                            ->get();

        foreach ($files as $file) {
            $file->delete();
        }

        return true; // succès
    }

}
