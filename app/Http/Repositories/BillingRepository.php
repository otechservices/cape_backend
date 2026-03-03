<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Billing;
 use App\Models\BillingResponse;
 use App\Utilities\Mailer;
use Str,Auth;


class BillingRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Billing
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Billing::class);
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

        if (Auth::user()?->roles()?->first()?->name == "admin") {
               $req = Billing::ignoreRequest(['per_page'])
            ->with('type')
            ->orderByDesc('created_at');
        }else{
            $req = Billing::ignoreRequest(['per_page'])
            ->with(['type','responses'])
            ->where('user_id',Auth::id())
            ->orderByDesc('created_at');
        }
     

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $billing = $req->paginate($per_page);
        } else {
            $billing = $req->get();
        }

        return $billing;

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
    public function makeStore($datas)
    {

        // Génération du token
       // $token = Str::random(40) . time();
       // $datas['token'] = $token;
        //$datas['user_id']=Auth::id();
        // Création du modèle
        $billing = new Billing($datas);
        $billing->save();

        // Envoi de l'email
        Mailer::sendSimple(
            'emails.support',
            ['user'=> $billing->user],
            "Demande d'assistance",
            "MASM CAPE",
            env('MAIL_FROM_ADDRESS')
        );

        // Retour direct du modèle
        return $billing;

    }


    public function storeResponse($datas)
    {

        $billing = new BillingResponse($datas);
        $billing->save();

        return $billing;
    }


    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Billing
    {
       
        $datas = $request->all();

        $billing = Billing::findOrFail($id);
        $billing->update($datas);

        return $billing;

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
        $billing = Billing::findOrFail($id);
        $billing->update(['is_active' => $status]);

        return $billing;

    }


    public function getDepartmentWithRelation()
    {
        $departments = Billing::with(['Municipalities.districts'])->get();
        return $departments;
    }

    public function show($id)
{
    $billing = Billing::with(['responses', 'type'])
        ->where('token', $id)
        ->firstOrFail();

    return $billing;
}



}
