<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
use App\Models\Backup;


class BackupRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Backup
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Backup::class);
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

        $req = Backup::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $backups = $req->paginate($per_page);
        } else {
            $backups = $req->get();
        }

        return $backups;

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
    public function makeStore(Request $request): Backup
    {
        try {
            Artisan::call('backup:run');

            $backup = new Backup([
                "name" => "sauvegarde du " . date('Y-m-d H:i:s')
            ]);
            $backup->save();

            return $backup;

        } catch (Exception $th) {
            // Relance l'exception pour que Laravel gère automatiquement le code HTTP 500
            throw $th;
        }
    }

}