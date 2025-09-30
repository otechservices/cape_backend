<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NaturePromotor;


class NaturePromotorController extends Controller
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
        $nature_promotors=NaturePromotor::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$nature_promotors
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
        
        $nature_promotors=NaturePromotor::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$nature_promotors
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
        $nature_promotors=NaturePromotor::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une périodicité",
            "data"=>$nature_promotors
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
       
        $nature_promotors=NaturePromotor::find($id);

        $nature_promotors->update($datas);

        $nature_promotors=NaturePromotor::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$nature_promotors
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
        $nature_promotors=NaturePromotor::find($id);
        $nature_promotors->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une périodicité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $nature_promotors=NaturePromotor::find($id);
        $nature_promotors->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }

}
