<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\ReferalControl;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ReferalControlRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var ReferalControl
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(ReferalControl::class);
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

        $query = ReferalControl::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            return $query->paginate($per_page);
        }

        return $query->get();
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
    public function makeStore($data): ReferalControl
    {
        $datas = $request->all();
        $datas['user_id'] = Auth::id();

        // Création du contrôle
        $referals = new ReferalControl($datas);
        $referals->save();

        // Création de la transmission
        $transmission = new TransmissionReferalControl([
            "referal_control_id" => $referals->id,
            "user_up" => Auth::id(),
            "user_down" => Auth::id(),
        ]);
        $transmission->save();

        return $referals;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): ReferalControl
    {
        $datas = $request->all();

        $referals = ReferalControl::findOrFail($id);
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
        $referals = ReferalControl::findOrFail($id);
        $referals->update(['is_active' => $status]);

        return $referals;

    }

    public function show($id)
    {
        $referals = ReferalControl::findOrFail($id);
        return $referals;
    }

    public function transUp( $id)
    {
        $roleName = Auth::user()->roles()->first()->name;

        switch ($roleName) 
        {
            case 'cps':
                $role = Role::where('name', 'ddasm')->first();
                $user = User::where("department_id", Auth::user()->cps->municipality?->department?->id)->first();

                if ($user) {
                    $lastTransmission = TransmissionReferalControl::where("referal_control_id", $id)->get()->last();
                    if ($lastTransmission) {
                        $lastTransmission->update(["isLast" => false]);
                    }

                    $transmission = new TransmissionReferalControl([
                        "control_id" => $id,
                        "user_up" => Auth::id(),
                        "user_down" => $user->id,
                    ]);
                    $transmission->save();

                } else {
                    abort(500, "Transmission non effectuée");
                }

                break;

            default:
                // Aucun traitement pour les autres rôles
            break;
        }

            // Retour direct pour indiquer le succès
            return true;
    }

}
