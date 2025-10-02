<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referal;
use App\Models\Cape;
use App\Models\Department;
use Auth;

class ReferalController extends Controller
{
    
    public function getWithCape()
    {

        if (request()->service_id) {

            $service_id=request()->service_id;


        $role=Auth::user()->roles()->first()->name;

        switch ($role) {
            case 'cps':
                $districtIds=Auth::user()->cps->districts->pluck('id');
                $referals=Cape::with([
                    'requete.referals.controls',
                    'requete.referals.myControls'=>function($q){$q->where("user_id",Auth::id())->withCount('transmissions');},
                    'requete.referals.transmittedControls'=>function($q){
                        $q->whereHas("transmissions",function($qu){
                        $qu->where("user_id","!=",Auth::id())->where("isLast",true)->where("user_down",Auth::id());
                    });},
                    'requete.TypeCape'])->whereHas('requete',function($q)use($districtIds,$service_id){
                    $q->whereIn('district_id',$districtIds)->where('is_authorized',true)->where('service_id',$service_id);
                })->get();
            break;
            case 'ddasm':
                $districtIds=[];
                $i=0;
                $depart=Department::find(Auth::user()->department_id);
                   foreach ($depart->municipalities as $key) {
                    foreach ($key->districts as $d) {
                        $districtIds[$i]= $d->id;
                        $i++;
                     }
                   }
        
                $referals=Cape::with([
                    'requete.referals.controls',
                    'requete.referals.myControls'=>function($q){$q->where("user_id",Auth::id())->withCount('transmissions');},
                    'requete.referals.transmittedControls'=>function($q){
                        $q->whereHas("transmissions",function($qu){
                        $qu->where("user_id","!=",Auth::id())->where("isLast",true)->where("user_down",Auth::id());
                    });},
                    'requete.TypeCape'])->whereHas('requete',function($q)use($districtIds,$service_id){
                    $q->whereIn('district_id',$districtIds)->where('is_authorized',true)->where('service_id',$service_id);
                })->get();
            break;
            case 'dfea':
                $referals=Cape::with(['requete.referals.controls','requete.TypeCape'])->whereHas('requete',function($q)use($service_id){
                    $q->where('service_id',$service_id);
                })->get();
                break;
            case 'ministre':
                $referals=Cape::with(['requete.referals.controls','requete.TypeCape'])->whereHas('requete',function($q)use($service_id){
                    $q->where('service_id',$service_id);
                })->get();
                break;

            
            default:
               $referals=[];
                break;
        }

    }
        
        return response()->json([
            "success"=>true,
            "message"=>"Liste des recommendations",
            "data"=>$referals
        ],200);    }

      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $referals=Referal::with('controls')->where('cape_id',Auth::user()->cape_id)->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des recommendations",
            "data"=>$referals
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
        $referals=Referal::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une recommendation",
            "data"=>$referals
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
        $referals=Referal::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une recommendation",
            "data"=>$referals
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
        $referals=Referal::find($id);
        $referals->update($datas);

        $referals=Referal::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$referals
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
        $referals=Referal::find($id);
        $referals->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une recommendation",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $referals=Referal::find($id);
        $referals->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }



}
