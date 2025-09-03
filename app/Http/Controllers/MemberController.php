<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $members=Member::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$members
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
        
        $members=Member::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un membre",
            "data"=>$members
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
        $members=Member::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un membre",
            "data"=>$members
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
       
        $members=Member::find($id);

        $members->update($datas);

        $members=Member::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un membre",
            "data"=>$members
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
        $members=Member::find($id);
        $members->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un membre",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $members=Member::find($id);
        $members->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation()
    {
        $members=Member::with(['Municipalities.districts'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$members
        ],200);
    }
}
