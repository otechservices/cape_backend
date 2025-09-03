<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use Auth;

class StaffController extends Controller
{
    

  

      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $staffs=Staff::where('cape_id',Auth::user()->cape_id)->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des personnels",
            "data"=>$staffs
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

        $staffs=Staff::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un personnel",
            "data"=>$staffs
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
        $staffs=Staff::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un personnel",
            "data"=>$staffs
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
       
        $staffs=Staff::find($id);
        $datas['birthdate'] =date_create($datas['birthdate']);

        $staffs->update($datas);

        $staffs=Staff::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un péridiocité",
            "data"=>$staffs
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
        $staffs=Staff::find($id);
        $staffs->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un personnel",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $staffs=Staff::find($id);
        $staffs->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }




}
