<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Info;
use Auth;

class InfoController extends Controller
{
    public function __construct() {
      
        $this->middleware('auth', ['except' => ['store']]);
    }
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $info=Info::with('TypeInfo')->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des info",
            "data"=>$info
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
        
        $info=Info::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un info",
            "data"=>$info
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
        $info=Info::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un info",
            "data"=>$info
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
       
        $info=Info::find($id);

        $info->update($datas);

        $info=Info::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un info",
            "data"=>$info
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
        $info=Info::find($id);
        $info->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un info",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $info=Info::find($id);
        $info->update(['has_answer' =>$status,'user_id' =>Auth::id()]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation()
    {
        $info=Info::with(['Municipalities.districts'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des info",
            "data"=>$info
        ],200);
    }
}
