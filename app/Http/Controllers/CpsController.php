<?php

namespace App\Http\Controllers;
use App\Models\Cps;
use App\Models\District;
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
        $cps=Cps::with('districts')->get();
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


    public function storeDistricts(Request $request)
    {

        foreach (json_decode($request->items) as $value) {
           District::find($value->id)->update(['cps_id'=>$request->id]);
        }


        return response()->json([
            "success"=>true,
            "message"=>"",
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
