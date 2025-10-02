<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Control;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ControlRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Control
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Control::class);
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

        $req = Control::ignoreRequest(['per_page'])
            ->with(['cape.requete', 'TypeControl'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $controls = $req->paginate($per_page);
        } else {
            $controls = $req->get();
        }

        return $controls;

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
    public function makeStore($data): Control
    {
        $datas = $request->all();
        $datas['user_id'] = Auth::id();

        $control = new Control($datas);
        $control->save();

        // Création de la transmission associée
        $transmission = new TransmissionControl([
            "control_id" => $control->id,
            "user_up" => Auth::id(),
            "user_down" => Auth::id(),
        ]);
        $transmission->save();

        return $control;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Control
    {
        $datas = $request->all();
        $control = Control::findOrFail($id);
        $control->update($datas);

         return $control;
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
        $control = Control::findOrFail($id);
        $control->update(['is_active' => $status]);

        return $control;
    }

    public function transUp($id)
    {
        $role = Auth::user()->roles()->first()->name;
        $transmission = null;

        switch ($role) {
            case 'cps':
                $user = User::where('department_id', Auth::user()->cps->municipality?->department?->id)->first();
                break;

            case 'ddasm':
                $roleObj = Role::where('name','dfea')->first();
                $user = User::role($roleObj)->first();
                break;

            default:
                $user = null;
                break;
        }

        if (!$user) {
            throw new \Exception("Transmission non effectuée");
        }

        $last = TransmissionControl::where('control_id', $id)->latest()->first();
        if ($last) {
            $last->update(['isLast' => false]);
        }

        $transmission = TransmissionControl::create([
            'control_id' => $id,
            'user_up'    => Auth::id(),
            'user_down'  => $user->id,
        ]);

        return $transmission; // Retour direct du modèle créé
    }

    public function show($id)
    {
        $control = Control::findOrFail($id);
        return $control;
    }


}
