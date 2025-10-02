<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeInfo;


class TypeInfoController extends Controller
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
        $type_info=TypeInfo::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des type info",
            "data"=>$type_info
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
        
        $type_info=TypeInfo::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$type_info
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
        $type_info=TypeInfo::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une périodicité",
            "data"=>$type_info
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
       
        $type_info=TypeInfo::find($id);

        $type_info->update($datas);

        $type_info=TypeInfo::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$type_info
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
        $type_info=TypeInfo::find($id);
        $type_info->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une périodicité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $type_info=TypeInfo::find($id);
        $type_info->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation()
    {
        $departments=TypeInfo::with(['Municipalities.districts'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des type info",
            "data"=>$departments
        ],200);
    }

}
