<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeSousGarderie;

class TypeSousGarderieController extends Controller
{
    
    public function __construct() {
      
        $this->middleware('auth', ['except' => ['index']]);
    }
  
       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $type_sous_garderies=TypeSousGarderie::with('TypeGarderie')->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$type_sous_garderies
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
        
        $type_sous_garderies=TypeSousGarderie::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$type_sous_garderies
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
        $type_sous_garderies=TypeSousGarderie::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une périodicité",
            "data"=>$type_sous_garderies
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
       
        $type_sous_garderies=TypeSousGarderie::find($id);

        $type_sous_garderies->update($datas);

        $type_sous_garderies=TypeSousGarderie::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$type_sous_garderies
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
        $type_sous_garderies=TypeSousGarderie::find($id);
        $type_sous_garderies->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une périodicité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $type_sous_garderies=TypeSousGarderie::find($id);
        $type_sous_garderies->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


}
