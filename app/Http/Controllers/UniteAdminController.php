<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UniteAdmin;

class UniteAdminController extends Controller
{
       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ua=UniteAdmin::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des unités administratives",
            "data"=>$ua
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
        
        $ua=UniteAdmin::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une unité administrative",
            "data"=>$ua
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
        $ua=UniteAdmin::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une unité administrative",
            "data"=>$ua
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
       
        $ua=UniteAdmin::find($id);

        $ua->update($datas);

        $ua=UniteAdmin::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une unité administrative",
            "data"=>$ua
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
        $ua=UniteAdmin::find($id);
        $ua->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une unité administrative",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $ua=UniteAdmin::find($id);
        $ua->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }

}
