<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Journal;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class JournalRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Journal
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Journal::class);
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

        // Construire la requête de base avec jointure et filtres
        $req = DB::table("activity_log")
            ->join('users', 'activity_log.causer_id', '=', 'users.id')
            ->select('activity_log.*', 'users.name')
            ->ignoreRequest(['per_page'])
            ->filter(array_filter(request()->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('activity_log.created_at');

        // Pagination ou récupération totale
        if (array_key_exists('per_page', request()->all())) {
            $per_page = request()->per_page;
            $activity_logs = $req->paginate($per_page);
        } else {
            $activity_logs = $req->get();
        }

        // Retourne directement la collection ou la pagination
        return $activity_logs;
    }

}
