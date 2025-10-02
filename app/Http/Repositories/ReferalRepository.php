<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Referal;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ReferalRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Referal
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Referal::class);
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

        $query = Referal::with('controls')
            ->where('cape_id', Auth::user()->cape_id)
            ->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            return $query->paginate($per_page);
        } else {
            return $query->get();
        }
    }

    /**
     * Récupère une fête spécifique.
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }


    public function getWithCape()
    {
        if ($service_id = request()->service_id) {

        $role = Auth::user()->roles()->first()->name;

        switch ($role) {
            case 'cps':
                $districtIds = Auth::user()->cps->districts->pluck('id');

                $referals = Cape::with([
                    'requete.referals.controls',
                    'requete.referals.myControls' => function($q) {
                        $q->where("user_id", Auth::id())->withCount('transmissions');
                    },
                    'requete.referals.transmittedControls' => function($q) {
                        $q->whereHas("transmissions", function($qu) {
                            $qu->where("user_id", "!=", Auth::id())
                            ->where("isLast", true)
                            ->where("user_down", Auth::id());
                        });
                    },
                    'requete.TypeCape'
                ])->whereHas('requete', function($q) use ($districtIds, $service_id) {
                    $q->whereIn('district_id', $districtIds)
                    ->where('is_authorized', true)
                    ->where('service_id', $service_id);
                })->get();
                break;

            case 'ddasm':
                $districtIds = [];
                $i = 0;
                $depart = Department::find(Auth::user()->department_id);
                foreach ($depart->municipalities as $municipality) {
                    foreach ($municipality->districts as $d) {
                        $districtIds[$i++] = $d->id;
                    }
                }

                $referals = Cape::with([
                    'requete.referals.controls',
                    'requete.referals.myControls' => function($q) {
                        $q->where("user_id", Auth::id())->withCount('transmissions');
                    },
                    'requete.referals.transmittedControls' => function($q) {
                        $q->whereHas("transmissions", function($qu) {
                            $qu->where("user_id", "!=", Auth::id())
                            ->where("isLast", true)
                            ->where("user_down", Auth::id());
                        });
                    },
                    'requete.TypeCape'
                ])->whereHas('requete', function($q) use ($districtIds, $service_id) {
                    $q->whereIn('district_id', $districtIds)
                    ->where('is_authorized', true)
                    ->where('service_id', $service_id);
                })->get();
                break;

            case 'dfea':
            case 'ministre':
                $referals = Cape::with(['requete.referals.controls', 'requete.TypeCape'])
                    ->whereHas('requete', function($q) use ($service_id) {
                        $q->where('service_id', $service_id);
                    })->get();
                break;

            default:
                $referals = collect(); // retourne une collection vide
                break;
        }

        return $referals;
        }

    }

    /**
     * Crée une nouvelle fête.
     */
    public function makeStore($data): Referal
    {
        $datas = $request->all();

        $referals = new Referal($datas);
        $referals->save();

        return $referals;

    }

    public function show($id)
    {
        $referals = Referal::findOrFail($id);
        return $referals;;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): Referal
    {
       $datas = $request->all();

        $referals = Referal::findOrFail($id);
        $referals->update($datas);

        return $referals;

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
        $activity_report=Referal::find($id);
        $data['status']=$status;
        if($status==2) $data['is_transmitted']=false;
        $activity_report->update($data);
        return $activity_report;
    }

   

}
