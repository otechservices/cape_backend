<?php

namespace App\Http\Controllers;
use App\Models\Cps;
use App\Models\District;
use App\Services\GupsAffectationService;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

class CpsController extends Controller
{


    public function __construct() {
      
        $this->middleware('auth', ['except' => ['exportPDF']]);
    }

       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cps=Cps::with(['districts','couverture'])->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$cps
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datas = $request->all();
        
        $cps=Cps::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$cps
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cps=Cps::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une périodicité",
            "data"=>$cps
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $datas=$request->all();
       
        $cps=Cps::find($id);

        $cps->update($datas);

        $cps=Cps::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$cps
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cps=Cps::find($id);
        $cps->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une périodicité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $cps=Cps::find($id);
        $cps->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation(Type $var = null)
    {
        $departments=Cps::with(['Municipalities'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$departments
        ],200);
    }


    /**
     * Ajoute des arrondissements à la couverture d'un GUPS.
     *
     * Un arrondissement peut être couvert par plusieurs GUPS : l'ajout ne le
     * retire plus à son GUPS actuel et ne déplace aucun dossier. Il ne devient
     * GUPS par défaut (celui qui reçoit les nouveaux dépôts) que si
     * l'arrondissement n'en avait pas.
     */
    public function storeDistricts(Request $request)
    {
        $cps = Cps::findOrFail($request->id);

        foreach (json_decode($request->items) as $value) {
           $district = District::find($value->id);
           $cps->couverture()->syncWithoutDetaching([$district->id]);

           if ($district->cps_id === null) {
               $district->update(['cps_id'=>$cps->id]);
           }
        }

        return response()->json([
            "success"=>true,
            "message"=>"Couverture mise à jour",
            "data"=>null
        ],200);
       
    }

    /**
     * Retire un arrondissement de la couverture d'un GUPS.
     *
     * Si c'était son GUPS par défaut, un autre GUPS qui le couvre prend le
     * relais. Les dossiers en instruction de ce GUPS dans l'arrondissement
     * passent au GUPS par défaut.
     */
    public function removeDistrict($id, $districtId)
    {
        $cps = Cps::findOrFail($id);
        $district = District::findOrFail($districtId);

        $cps->couverture()->detach($district->id);

        if ((int) $district->cps_id === (int) $cps->id) {
            $relais = DB::table('cps_district')->where('district_id', $district->id)->orderBy('id')->value('cps_id');
            $district->update(['cps_id'=>$relais]);
        }

        $deplaces = app(GupsAffectationService::class)
            ->reaffecterArrondissement($district->id, "arrondissement {$district->name} retiré de la couverture du {$cps->name}");

        return response()->json([
            "success"=>true,
            "message"=>"Arrondissement retiré de la couverture"
                .($deplaces ? " : $deplaces dossier(s) confié(s) au GUPS par défaut" : '')
                .($district->cps_id === null ? ". Attention : plus aucun GUPS ne couvre {$district->name}" : ''),
            "data"=>null
        ],200);
    }

    /** Fait d'un GUPS le GUPS par défaut d'un arrondissement qu'il couvre. Aucun dossier ne bouge. */
    public function setDefaultDistrict($id, $districtId)
    {
        $cps = Cps::findOrFail($id);
        $district = District::findOrFail($districtId);

        $cps->couverture()->syncWithoutDetaching([$district->id]);
        $district->update(['cps_id'=>$cps->id]);

        return response()->json([
            "success"=>true,
            "message"=>"{$cps->name} reçoit désormais les nouveaux dossiers de {$district->name}",
            "data"=>null
        ],200);
    }


    public function exportPDF()
    {
        $datas=Cps::all();

        $pdf=Pdf::loadView('pdf.cps', [
            "datas"=>$datas,
        ]);

        return $pdf->download('liste_cps.pdf');
    }
}
