<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Role;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class RoleRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Role
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Role::class);
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
        if (Auth::user()->hasRole('Admin')) {
            $req = Role::query();
        } else {
            $req = Role::whereNotIn('name', ['Admin', 'Admin Sectoriel']);
        }

        // Gestion de la pagination
        $per_page = 10;
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page');
            return $req->orderByDesc('created_at')->paginate($per_page);
        } else {
            return $req->orderByDesc('created_at')->get();
        }
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

    public function makeStore(Request $request): Role
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
        ]);

        $role = new Role([
            'name' => $request->input('name')
        ]);
        $role->save();

        return $role;
    }


    public function show(Role $role)
    {
        $role = $role;
        return $role;
    }   

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Role $role, Request $request): Role
    {
       $this->validate($request, [
            'name' => 'required',
        ]);
        $role->update($request->only('name'));
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


}
