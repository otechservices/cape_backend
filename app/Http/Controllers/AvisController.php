<?php

namespace App\Http\Controllers;
use App\Models\Avis;
use Illuminate\Http\Request;

class AvisController extends Controller
{
      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $avis=Avis::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des avis",
            "data"=>$avis
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
        
        $avis=Avis::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un avis",
            "data"=>$avis
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
        $avis=Avis::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un avis",
            "data"=>$avis
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
       
        $avis=Avis::find($id);

        $avis->update($datas);

        $avis=Avis::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un avis",
            "data"=>$avis
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
        $avis=Avis::find($id);
        $avis->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un avis",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $avis=Avis::find($id);
        $avis->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation()
    {
        $avis=Avis::with(['Municipalities.districts'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des avis",
            "data"=>$avis
        ],200);
    }
}
