<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Profile;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ProfileRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Profile
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Profile::class);
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

        // Requête pour récupérer les rôles avec permissions
        $reqRoles = Role::with('permissions')
            ->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Pagination ou récupération complète pour les rôles
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request->per_page;
            $roles = $reqRoles->paginate($per_page);
        } else {
            $roles = $reqRoles->get();
        }

        // Permissions, récupération complète sans filtre (si tu veux filtrer, on peut aussi appliquer filter ici)
        $permissions = Permission::all();

        // Retour direct des modèles
        return [
            'roles' => $roles,
            'permissions' => $permissions
        ];

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
    public function makeStore($data): Profile
    {
        $role = Role::find($request->id);
        $permission = Permission::find($request->permission_id);

        $role->givePermissionTo($permission);

        return $role;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): Profile
    {
        $role = Role::findOrFail($id);
        $permission = Permission::find($request->id);

        $role->revokePermissionTo($permission);

        return $role;
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
        //
    }

}
