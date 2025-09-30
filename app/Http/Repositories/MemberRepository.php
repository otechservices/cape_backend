<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Member;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class MemberRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Member
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Member::class);
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

        // Construire la requête de base avec filtres
        $req = Member::ignoreRequest(['per_page'])
            ->filter(array_filter(request()->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Pagination ou récupération totale
        if (array_key_exists('per_page', request()->all())) {
            $per_page = request()->per_page;
            $members = $req->paginate($per_page);
        } else {
            $members = $req->get();
        }

        // Retourne directement la collection ou la pagination
        return $members;
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
    public function makeStore(Request $request): Member
    {
        $datas = $request->all();
        $members = new Member($datas);
        $members->save();

        return $members;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Member
    {
       
        $datas = $request->all();
        $members = Member::findOrFail($id);
        $members->update($datas);

        return $members;
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

   public function show($id)
    {
        $members = Member::find($id);
        return $members;
    }

    public function setStatus($id, $status)
    {
        $members = Member::find($id);

        if ($members) {
            $members->update([
                'is_active' => $status
            ]);
            return $members;
        }
        return null;
    }

    public function getDepartmentWithRelation()
    {
        $members = Member::with(['Municipalities.districts'])->get();
        return $members;
    }

}
