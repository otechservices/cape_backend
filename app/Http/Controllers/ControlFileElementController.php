<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ControlFileElement;

class ControlFileElementController extends Controller
{
    public function __construct() {
      
       // $this->middleware('auth', ['except' => ['index']]);
    }
    
/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataR=ControlFileElement::with('service')->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des éléments de fiche de contrôle",
            "data"=>$dataR
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
        
        $dataR=ControlFileElement::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un élément de fiche de contrôle",
            "data"=>$dataR
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
        $dataR=ControlFileElement::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un élément de fiche de contrôle",
            "data"=>$dataR
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
       
        $dataR=ControlFileElement::find($id);

        $dataR->update($datas);

        $dataR=ControlFileElement::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$dataR
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
        $dataR=ControlFileElement::find($id);
        $dataR->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un élément de fiche de contrôle",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $dataR=ControlFileElement::find($id);
        $dataR->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getAllActives($serviceId)
    {
        $dataR=ControlFileElement::where('is_active',true)->where('service_id',$serviceId)->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des éléments de fiche de contrôle",
            "data"=>$dataR
        ],200);
    }
}
