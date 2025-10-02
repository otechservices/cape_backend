<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Statistique;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class StatistiqueRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Statistique
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Statistique::class);
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
        $service_id = request()->service_id ?? null;

        switch ($type) {
            case 'cape-inscrits':
                $req = $this->getForCape($service_id)
                    ->ignoreRequest(['per_page'])
                    ->filter(array_filter(request()->all(), function ($k) {
                        return $k != 'page';
                    }, ARRAY_FILTER_USE_KEY))
                    ->orderByDesc('created_at');
                break;

            case 'cape-autorises':
                $req = $this->getForCapeAuthorized($service_id)
                    ->ignoreRequest(['per_page'])
                    ->filter(array_filter(request()->all(), function ($k) {
                        return $k != 'page';
                    }, ARRAY_FILTER_USE_KEY))
                    ->orderByDesc('created_at');
                break;

            case 'controls':
                $req = $this->getForControl($service_id)
                    ->ignoreRequest(['per_page'])
                    ->filter(array_filter(request()->all(), function ($k) {
                        return $k != 'page';
                    }, ARRAY_FILTER_USE_KEY))
                    ->orderByDesc('created_at');
                break;

            case 'cps':
                $req = $this->getForCps($agg, $service_id)
                    ->ignoreRequest(['per_page'])
                    ->filter(array_filter(request()->all(), function ($k) {
                        return $k != 'page';
                    }, ARRAY_FILTER_USE_KEY))
                    ->orderByDesc('created_at');
                break;

            default:
                return collect(); // retourne une collection vide
        }

        // Pagination si demandée
        if (array_key_exists('per_page', request()->all())) {
            $per_page = request('per_page');
            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    public function getForCape($service_id)
    {
        $role = Auth::user()->roles()->first()->name; // récupère le rôle de l'utilisateur
        $datas = [];

        switch ($role) {
            case 'cps':
                $districts = Auth::user()->cps->districts;
                foreach ($districts as $i => $district) {
                    $datas['data']['label'] = "Capes inscrits";
                    $datas['data']['data'][$i] = Requete::where('district_id', $district->id)
                                                        ->where('service_id', $service_id)
                                                        ->count();
                    $datas['labels'][$i] = $district->name;
                }
                break;

            case 'ddasm':
                $departDistricts = [];
                $i = 0;
                $depart = Department::find(Auth::user()->department_id);
                foreach ($depart->municipalities as $municipality) {
                    foreach ($municipality->districts as $d) {
                        $departDistricts[$i] = $d;
                        $i++;
                    }
                }

                foreach ($departDistricts as $i => $district) {
                    $counted = Requete::where('district_id', $district->id)
                                    ->where('service_id', $service_id)
                                    ->count();
                    $datas['data']['label'] = "Capes inscrits";
                    $datas['data']['data'][$i] = $counted;
                    $datas['labels'][$i] = $district->name;

                    $datas['ordered'][$i] = [
                        'municipality_id' => $district->municipality_id,
                        'district_id' => $district->id,
                        'label' => $district->name,
                        'value' => $counted
                    ];
                }
                break;

            case 'dfea':
                $departDistricts = [];
                $departIds = [];
                $i = 0;
                $departs = Department::all();

                foreach ($departs as $depart) {
                    foreach ($depart->municipalities as $municipality) {
                        foreach ($municipality->districts as $d) {
                            $departDistricts[$i] = $d;
                            $departIds[$i] = $depart->id;
                            $i++;
                        }
                    }
                }

                foreach ($departDistricts as $i => $district) {
                    $counted = Requete::where('district_id', $district->id)
                                    ->where('service_id', $service_id)
                                    ->count();
                    $datas['data']['label'] = "Capes inscrits";
                    $datas['data']['data'][$i] = $counted;
                    $datas['labels'][$i] = $district->name;

                    $datas['ordered'][$i] = [
                        'municipality_id' => $district->municipality_id,
                        'district_id' => $district->id,
                        'label' => $district->name,
                        'value' => $counted,
                        'department_id' => $departIds[$i]
                    ];
                }
                break;

            default:
                $districts = District::all();
                foreach ($districts as $i => $district) {
                    $datas['data']['label'] = "Capes inscrits";
                    $datas['data']['data'][$i] = Requete::where('district_id', $district->id)
                                                        ->where('service_id', $service_id)
                                                        ->count();
                    $datas['labels'][$i] = $district->name;
                }
                break;
        }

        return $datas;
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


    public function getForCapeAuthorized($service_id)
    {
        $datas = [];
        $role = Auth::user()->roles()->first()->name;

        switch ($role) {
            case 'cps':
                $districts = Auth::user()->cps->districts;
                foreach ($districts as $i => $district) {
                    $datas['data']['label'] = "Capes inscrits";
                    $datas['data']['data'][$i] = Cape::whereHas("requete", function ($q) use ($district, $service_id) {
                        $q->where('service_id', $service_id)
                        ->where('district_id', $district->id);
                    })->count();
                    $datas['labels'][$i] = $district->name;
                }
                break;

            case 'ddasm':
                $departDistricts = [];
                $i = 0;
                $depart = Department::find(Auth::user()->department_id);
                foreach ($depart->municipalities as $municipality) {
                    foreach ($municipality->districts as $district) {
                        $departDistricts[$i++] = $district;
                    }
                }

                foreach ($departDistricts as $i => $district) {
                    $datas['data']['label'] = "Capes autorisés";
                    $counted = Cape::whereHas("requete", function ($q) use ($district, $service_id) {
                        $q->where('service_id', $service_id)
                        ->where('district_id', $district->id);
                    })->count();

                    $datas['data']['data'][$i] = $counted;
                    $datas['labels'][$i] = $district->name;

                    $datas['ordered'][$i] = [
                        'municipality_id' => $district->municipality_id,
                        'district_id' => $district->id,
                        'label' => $district->name,
                        'value' => $counted,
                    ];
                }
                break;

            case 'dfea':
                $departDistricts = [];
                $departIds = [];
                $i = 0;
                $departs = Department::all();
                foreach ($departs as $depart) {
                    foreach ($depart->municipalities as $municipality) {
                        foreach ($municipality->districts as $district) {
                            $departDistricts[$i] = $district;
                            $departIds[$i] = $depart->id;
                            $i++;
                        }
                    }
                }

                foreach ($departDistricts as $i => $district) {
                    $datas['data']['label'] = "Capes autorisés";
                    $counted = Cape::whereHas("requete", function ($q) use ($district, $service_id) {
                        $q->where('service_id', $service_id)
                        ->where('district_id', $district->id);
                    })->count();

                    $datas['data']['data'][$i] = $counted;
                    $datas['labels'][$i] = $district->name;

                    $datas['ordered'][$i] = [
                        'municipality_id' => $district->municipality_id,
                        'district_id' => $district->id,
                        'label' => $district->name,
                        'value' => $counted,
                        'department_id' => $departIds[$i],
                    ];
                }
                break;

            default:
                $districts = District::all();
                foreach ($districts as $i => $district) {
                    $datas['data']['label'] = "Capes inscrits";
                    $datas['data']['data'][$i] = Cape::whereHas("requete", function ($q) use ($district, $service_id) {
                        $q->where('service_id', $service_id)
                        ->where('district_id', $district->id);
                    })->count();
                    $datas['labels'][$i] = $district->name;
                }
                break;
        }

        return $datas;
    }


    public function getForControl($service_id)
    {
        $datas = [];
        $role = Auth::user()->roles()->first()->name;

        switch ($role) {
            case 'cps':
                $districts = Auth::user()->cps->districts->pluck('id')->toArray();

                $capes = Cape::with('requete')->whereHas('requete', function ($q) use ($districts, $service_id) {
                    $q->where('service_id', $service_id)
                    ->whereIn('district_id', $districts);
                })->get();

                foreach ($capes as $cape) {
                    $datas['data'][] = [
                        'count' => $cape->controls->count(),
                        'name' => $cape->requete?->name,
                    ];
                }
                break;

            case 'ddasm':
                $departDistricts = [];
                $depart = Department::find(Auth::user()->department_id);

                foreach ($depart->municipalities as $municipality) {
                    foreach ($municipality->districts as $district) {
                        $departDistricts[] = $district->id;
                    }
                }

                $capes = Cape::with('requete')->whereHas('requete', function ($q) use ($departDistricts, $service_id) {
                    $q->where('service_id', $service_id)
                    ->whereIn('district_id', $departDistricts);
                })->get();

                foreach ($capes as $cape) {
                    $datas['data'][] = [
                        'count' => $cape->controls->count(),
                        'name' => $cape->requete?->name,
                    ];
                }
                break;

            default:
                // Optionnel : gérer les autres rôles si nécessaire
                break;
        }

        return $datas;
    }


    public function getForCps($agg, $service_id)
    {
        // Récupération des IDs des districts
        $districtIds = $agg 
            ? Cps::find($agg)->municipality->districts->pluck('id') 
            : Cps::first()->municipality->districts->pluck('id');

        $start_year = 2023;
        $end_year = date('Y');
        $datas = [];

        for ($i = 0; $i <= ($end_year - $start_year); $i++) {
            $year = $start_year + $i;
            $intervalDate = [
                date("Y-01-01 00:00:00", strtotime("$year-01-01")),
                date("Y-12-31 23:59:59", strtotime("$year-12-31"))
            ];

            // Initialisation des labels
            $datas['data'][0]['label'] = "Capes inscrits";
            $datas['data'][1]['label'] = "Capes autorisés";

            // Comptage des requêtes et capes
            $datas['data'][0]['data'][$i] = Requete::whereBetween('created_at', $intervalDate)
                ->whereIn('district_id', $districtIds)
                ->where('service_id', $service_id)
                ->count();

            $datas['data'][1]['data'][$i] = Cape::whereBetween('created_at', $intervalDate)
                ->whereHas('requete', function ($q) use ($districtIds, $service_id) {
                    $q->where('service_id', $service_id)
                    ->whereIn('district_id', $districtIds);
                })
                ->count();

            // Label pour l'année
            $datas['labels'][$i] = $year;
        }

        return $datas;
    }

}
