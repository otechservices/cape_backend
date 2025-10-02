<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Permissions;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class PermissionsRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Permissions
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Permissions::class);
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

        // Construire la requête avec filtres et tri
        $req = Permission::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Pagination ou récupération complète
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request->per_page;
            $permissions = $req->paginate($per_page);
        } else {
            $permissions = $req->get();
        }

        return $permissions;
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
    public function makeStore(Request $request): Permissions
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        $permission = new Permission($request->only('name'));
        $permission->save();

        return $permission; 
    }
    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, Permission $permissions): Permissions
    {
       $request->validate([
        'name' => 'required|unique:permissions,name,' . $permission->id
        ]);

        $permission->update($request->only('name'));

        return $permission;

    }

    /**
     * Supprime une fête.
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

}
