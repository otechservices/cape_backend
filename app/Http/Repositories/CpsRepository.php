<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Cps;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class CpsRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Cps
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Cps::class);
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

        $req = Cps::ignoreRequest(['per_page'])
            ->with('districts')
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            $cps = $req->paginate($per_page);
        } else {
            $cps = $req->get();
        }

        return $cps;

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
    public function makeStore(Request $request): Cps
    {
       $datas = $request->all();

        $cps = new Cps($datas);
        $cps->save();

        return $cps;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Cps
    {
        $datas = $request->all();

        $cps = Cps::findOrFail($id);
        $cps->update($datas);

        return $cps;

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

    public function setStatus($id, $status)
    {
        $cps = Cps::findOrFail($id);
        $cps->update(['is_active' => $status]);

        return $cps;
    }


    public function getDepartmentWithRelation()
    {
        $departments = Cps::with(['Municipalities'])->get();
        return $departments;
    }

    public function storeDistricts(Request $request)
    {
        foreach (json_decode($request->items) as $value) {
            $district = District::findOrFail($value->id);
            $district->update(['cps_id' => $request->id]);
        }
        return true; // ou retourner une collection si nécessaire
    }

    public function exportPDF()
    {
        $datas = Cps::all();
        $pdf = Pdf::loadView('pdf.cps', [
            'datas' => $datas,
        ]);

        return $pdf->download('liste_cps.pdf');
    }


    public function show($id)
    {
        $cps = Cps::findOrFail($id);
        return $cps;
    }


}
