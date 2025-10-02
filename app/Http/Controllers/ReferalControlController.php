<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReferalControl;
use App\Models\User;
use App\Models\TransmissionReferalControl;
use Spatie\Permission\Models\Role;

use Auth;

class ReferalControlController extends Controller
{
 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $referals=ReferalControl::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
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
        $datas ['user_id']=Auth::id();
        $referals=ReferalControl::create($datas);
        TransmissionReferalControl::create([
            "referal_control_id"=>$referals->id,
            "user_up"=>Auth::id(),
            "user_down"=>Auth::id(),
        ]);
        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un contrôle",
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
        $referals=ReferalControl::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un contrôle",
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
       
        $referals=ReferalControl::find($id);

        $referals->update($datas);

        $referals=ReferalControl::find($id);

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
        $referals=ReferalControl::find($id);
        $referals->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un contrôle",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $referals=ReferalControl::find($id);
        $referals->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }


    public function transUp( $id)
    {
        $role=Auth::user()->roles()->first()->name;
        switch ($role) {
            case 'cps':
                $role=Role::where('name','ddasm')->first();
                $user=User::where("department_id",Auth::user()->cps->municipality?->department?->id)->first();
               if ($user) {
                $checkLast=TransmissionReferalControl::where("referal_control_id",$id)->get()->last();
                if ($checkLast) {
                    $checkLast->update(["isLast"=>false]);
                }
                TransmissionReferalControl::create([
                    "control_id"=>$id,
                    "user_up"=>Auth::id(),
                    "user_down"=>$user->id,
                ]);
               }else {
                return response()->json([
                    "success"=>false,
                    "message"=>"Transmission non effectuée",
                    "data"=>null
                ],500);
               }

               
                break;
            
            default:
                # code...
                break;
        }


        return response()->json([
            "success"=>true,
            "message"=>"Transmission effectué",
            "data"=>null
        ],200);
    }

}
