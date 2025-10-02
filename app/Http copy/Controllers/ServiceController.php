<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
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
        $services=Service::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$services
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
        
        $services=Service::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$services
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
        $services=Service::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une périodicité",
            "data"=>$services
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
       
        $services=Service::find($id);

        $services->update($datas);

        $services=Service::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$services
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
        $services=Service::find($id);
        $services->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une périodicité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $services=Service::find($id);
        $services->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation()
    {
        $services=Service::with(['Municipalities.districts'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$services
        ],200);
    }
}
