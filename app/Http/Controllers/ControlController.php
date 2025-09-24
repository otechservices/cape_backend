<?php

namespace App\Http\Controllers;
use App\Models\Control;
use App\Models\User;
use App\Models\TransmissionControl;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Auth;

class ControlController extends Controller
{
    
  

      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $controls=Control::with(['cape.requete','TypeControl'])->get();;

        return response()->json([
            "success"=>true,
            "message"=>"Liste des contrôles",
            "data"=>$controls
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
        $datas['user_id'] =Auth::id();

        $controls=Control::create($datas);


        TransmissionControl::create([
            "control_id"=>$controls->id,
            "user_up"=>Auth::id(),
            "user_down"=>Auth::id(),
        ]);
        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un contrôle",
            "data"=>$controls
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
        $controls=Control::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un contrôle",
            "data"=>$controls
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
       
        $controls=Control::find($id);

        $controls->update($datas);

        $controls=Control::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un contrôle",
            "data"=>$controls
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
        $controls=Control::find($id);
        $controls->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un contrôle",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $controls=Control::find($id);
        $controls->update(['is_active' =>$status]);
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
                $checkLast=TransmissionControl::where("control_id",$id)->get()->last();
                if ($checkLast) {
                    $checkLast->update(["isLast"=>false]);
                }
                TransmissionControl::create([
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
                case 'ddasm':
                    $role=Role::where('name','dfea')->first();
                    $user=User::role($role)->first();
                   if ($user) {
                    $checkLast=TransmissionControl::where("control_id",$id)->get()->last();
                    if ($checkLast) {
                        $checkLast->update(["isLast"=>false]);
                    }
                    TransmissionControl::create([
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
