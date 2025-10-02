<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billing;
use App\Models\BillingResponse;
use App\Models\BillingFile;
use App\Utilities\Mailer;
use App\Utilities\FileStorage;
use Str;

class BillingController extends Controller
{
    public function __construct() {
      
        $this->middleware('auth', ['except' => ['store','setStatus','show','storeResponse']]);
    }

  
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $billing=Billing::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des type info",
            "data"=>$billing
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
        $token=Str::random(40).time();
        $datas['token']=$token;
        $billing=Billing::create($datas);

        Mailer::sendSimple('emails.support',["token"=>$token],"Demande d'assistance",$request->name,$request->email);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$billing
        ],200);
    }
     
    public function storeResponse(Request $request)
    {
        $datas = $request->all();
        
        $billing=BillingResponse::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$billing
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
        $billing=Billing::with(['responses','type'])->where('token',$id)->first();
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une périodicité",
            "data"=>$billing
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
       
        $billing=Billing::find($id);

        $billing->update($datas);

        $billing=Billing::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$billing
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
        $billing=Billing::find($id);
        $billing->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une périodicité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $billing=Billing::find($id);
        $billing->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation()
    {
        $departments=Billing::with(['Municipalities.districts'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des type info",
            "data"=>$departments
        ],200);
    }


}
