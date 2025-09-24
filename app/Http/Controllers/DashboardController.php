<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requete;
use App\Models\Resident;
use App\Models\Referal;
use App\Models\ReferalControl;
use App\Models\User;
use Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role=Auth::user()->roles()->first()->name;
        $data=[];
        $idUser=Auth::id();


        switch ($role) {
            case 'admin':
                $data['users']=User::count();
                $data['authorized']=Requete::where('is_authorized',true)->count();
                $data['registered']=Requete::count();
                break;
            case 'cps':
                $data['new']=Requete::where('status',0)->count();
                $data['success']=Requete::where('status',7)->count();
                $data['pending']=Requete::where('status',1)->count();
                $data['referals']=Referal::where('is_reached',false)->count();
                $data['authorized']=Requete::where('is_authorized',true)->count();
                $data['registered']=Requete::count();
                break;
            case 'member':
                $all=Auth::user()->sm->session->requetes->count();
                $treated=Auth::user()->sm->avis->count();
                $data['all']=Auth::user()->sm->session->requetes->count();
                $data['treated']=$all-$treated;
                break;
                case 'cape':
                    $data['residents']=Resident::where('cape_id',Auth::user()->cape->id)->count();
                    $data['referals']=Referal::where('is_reached',false)->where('cape_id',Auth::user()->cape->id)->count();
                break;
                case 'ministre':
                    $data['authorized']=Requete::where('is_authorized',true)->count();
                    $data['pending']=Requete::where('is_authorized',null)->where('has_agreemant',true)->count();
                    $data['total']=Requete::where('has_agreemant',"!=",null)->count();
                    $data['referals']=Referal::where('is_reached',false)->count();
                    $data['registered']=Requete::count();
                    $data['pending_validation']=Requete::where('has_agreemant',"!=",null)->where('is_authorized',null)->count();

                    break;
                    case 'dfea':
                        $pending_validation=Requete::with(['files','reponses','parcours','affectation','TypeCape'])->where('status',6)->whereHas('affectations', function($q) use($idUser) {
                            $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
                            })->orderBy("id",'desc')->count();
                        $data['pending_validation']=$pending_validation;
                        $data['authorized']=Requete::where('is_authorized',true)->count();
                        $data['pending']=Requete::where('is_authorized',null)->where('has_agreemant',true)->count();
                        $data['total']=Requete::where('has_agreemant',"!=",null)->count();
                        $data['referals']=Referal::where('is_reached',false)->count();
                        $data['registered']=Requete::count();
                        break;
                    case 'service':
                        $pending_validation=Requete::with(['files','reponses','parcours','affectation','TypeCape'])->where('status',6)->whereHas('affectations', function($q) use($idUser) {
                            $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
                            })->orderBy("id",'desc')->count();
                        $data['pending_validation']=$pending_validation;
                        $data['authorized']=Requete::where('is_authorized',true)->count();
                        $data['pending']=Requete::where('is_authorized',null)->where('has_agreemant',true)->count();
                        $data['total']=Requete::where('has_agreemant',"!=",null)->count();
                        $data['referals']=Referal::where('is_reached',false)->count();
                        $data['registered']=Requete::count();
                        break;
                    case 'ddasm':
                        $pending_validation=Requete::with(['files','reponses','parcours','affectation','TypeCape'])->where('status',5)->whereHas('affectations', function($q) use($idUser) {
                            $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
                            })->orderBy("id",'desc')->count();
                        $data['pending_validation']=$pending_validation;
                        $data['controls']=ReferalControl::where('user_id',Auth::id())->count();
                        $data['total']=Requete::where('has_agreemant',"!=",null)->count();
                        break;
            default:
            $data=[];
            break;
        }
        return response()->json([
            "success"=>true,
            "message"=>"",
            "data"=>$data
        ], 200);
    }
}
