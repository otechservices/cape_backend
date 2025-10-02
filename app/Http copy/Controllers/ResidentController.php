<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resident;
use Auth;

class ResidentController extends Controller
{
    
  

      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $residents=Resident::where('cape_id',Auth::user()->cape->id)->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des personnels",
            "data"=>$residents
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
        $datas['birthdate'] =date_create($datas['birthdate']);
        $datas['cape_id'] =Auth::user()->cape->id;

        $residents=Resident::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un personnel",
            "data"=>$residents
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
        $residents=Resident::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un personnel",
            "data"=>$residents
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
       
        $residents=Resident::find($id);
        $datas['birthdate'] =date_create($datas['birthdate']);

        $residents->update($datas);

        $residents=Resident::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un pensionnaire",
            "data"=>$residents
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
        $residents=Resident::find($id);
        $residents->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un personnel",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $residents=Resident::find($id);
        $residents->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }




}
