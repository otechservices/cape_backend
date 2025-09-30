<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Dashboard;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class DashboardRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Dashboard
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Dashboard::class);
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
        $role = Auth::user()->roles()->first()->name;
        $data = [];
        $idUser = Auth::id();

        switch ($role) {
                case 'admin':
                    $data['users'] = User::count();
                    $data['authorized'] = Requete::where('is_authorized', true)->count();
                    $data['registered'] = Requete::count();
                    break;

                case 'cps':
                    $data['new'] = Requete::where('status', 0)->count();
                    $data['success'] = Requete::where('status', 7)->count();
                    $data['pending'] = Requete::where('status', 1)->count();
                    $data['referals'] = Referal::where('is_reached', false)->count();
                    $data['authorized'] = Requete::where('is_authorized', true)->count();
                    $data['registered'] = Requete::count();
                    break;

                case 'member':
                    $all = Auth::user()->sm->session->requetes->count();
                    $treated = Auth::user()->sm->avis->count();
                    $data['all'] = $all;
                    $data['treated'] = $all - $treated;
                    break;

                case 'cape':
                    $capeId = Auth::user()->cape->id;
                    $data['residents'] = Resident::where('cape_id', $capeId)->count();
                    $data['referals'] = Referal::where('is_reached', false)
                                            ->where('cape_id', $capeId)
                                            ->count();
                    break;

                case 'ministre':
                    $data['authorized'] = Requete::where('is_authorized', true)->count();
                    $data['pending'] = Requete::where('is_authorized', null)
                                            ->where('has_agreemant', true)->count();
                    $data['total'] = Requete::where('has_agreemant', '!=', null)->count();
                    $data['referals'] = Referal::where('is_reached', false)->count();
                    $data['registered'] = Requete::count();
                    $data['pending_validation'] = Requete::where('has_agreemant', '!=', null)
                                                        ->where('is_authorized', null)
                                                        ->count();
                    break;

                case 'dfea':
                case 'service':
                case 'ddasm':
                    $statusFilter = ($role === 'ddasm') ? 5 : 6;
                    $pending_validation = Requete::with(['files','reponses','parcours','affectation','TypeCape'])
                        ->where('status', $statusFilter)
                        ->whereHas('affectations', function($q) use($idUser) {
                            $q->where('user_down', $idUser)
                            ->where('isLast', true);
                        })
                        ->orderBy('id', 'desc')
                        ->count();

                    $data['pending_validation'] = $pending_validation;
                    $data['authorized'] = Requete::where('is_authorized', true)->count();
                    $data['pending'] = Requete::where('is_authorized', null)
                                            ->where('has_agreemant', true)->count();
                    $data['total'] = Requete::where('has_agreemant', '!=', null)->count();
                    $data['referals'] = Referal::where('is_reached', false)->count();
                    $data['registered'] = Requete::count();

                    if ($role === 'ddasm') {
                        $data['controls'] = ReferalControl::where('user_id', $idUser)->count();
                    }
                    break;

                default:
                    $data = [];
                    break;
            }

        // Pagination logique comme dans getAll($request)
        $per_page = 10;
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            return collect($data)->paginate($per_page); // Transformer $data en collection pour paginer
        }

        return $data;
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
