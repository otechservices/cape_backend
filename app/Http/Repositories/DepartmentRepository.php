<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Department;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class DepartmentRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Department
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Department::class);
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

        $req = Department::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $departments = $req->paginate($per_page);
        } else {
            $departments = $req->get();
        }

        return $departments;
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
    public function makeStore(Request $request): Department
    {
        $datas = $request->all();

        $department = new Department($datas);
        $department->save();

        return $department;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Department
    {
        $department = Department::findOrFail($id);
        $department->update($request->all());

        return $department;
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
        $department = Department::findOrFail($id);
        return $department;
    }

    public function setStatus($id, $status)
    {
        $department = Department::findOrFail($id);
        $department->update(['is_active' => $status]);

        return $department;
    }

    public function getDepartmentWithRelation()
    {
        $departments = Department::with(['Municipalities.districts'])
            ->orderBy('name', 'asc')
            ->get();

        return $departments;
    }


}
