<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requete;
use App\Models\Cape;
use App\Models\Cps;
use App\Models\District;
use App\Models\Department;
use Auth;

class StatistiqueController extends Controller
{
    

    public function index($type,$agg=null)
    {
       $datas=[];


       if (request()->service_id) {
        $service_id=request()->service_id;
       switch ($type) {
        case 'cape-inscrits':
           return $this->getForCape($service_id);
            break;
        case 'cape-autorises':
           return $this->getForCapeAuthorized($service_id);
            break;
        case 'controls':
           return $this->getForControl($service_id);
            break;
        
        case 'cps':
           return $this->getForCps($agg,$service_id);
            break;
        
        default:
        $datas=[];
        break;
       }
    
    }



       return response()->json([
        "success"=>true,
        "message"=>"Statistique $type",
        "data"=>$datas
    ],200);

    }



   public function getForCape($service_id)
   {
    $role=Auth::user()->roles()->first()->name;
    $datas=[];


    switch ($role) {
        case 'cps':
            $districts=Auth::user()->cps->districts;
            for ($i=0; $i < $districts->count(); $i++) { 
                $datas['data']['label']="Capes inscrits";
                $datas['data']['data'][$i]=Requete::where('district_id',$districts[$i]->id)->where('service_id',$service_id)->count();
                $datas['labels'][$i]=$districts[$i]->name;
            }
            break;

            case 'ddasm':
                $departDistricts=[];
                $i=0;
                $depart=Department::find(Auth::user()->department_id);
                   foreach ($depart->municipalities as $key) {
                    foreach ($key->districts as $d) {
                        $departDistricts[$i]= $d;
                        $i++;
                     }
                   }
                for ($i=0; $i <count( $departDistricts); $i++) { 
                    $counted=Requete::where('district_id',$departDistricts[$i]->id)->where('service_id',$service_id)->count();
                    $datas['data']['label']="Capes inscrits";
                    $datas['data']['data'][$i]=$counted;
                    $datas['labels'][$i]=$departDistricts[$i]->name;

                    $datas['ordered'][$i]['municipality_id']=$departDistricts[$i]->municipality_id;
                    $datas['ordered'][$i]['district_id']=$departDistricts[$i]->id;
                    $datas['ordered'][$i]['label']=$departDistricts[$i]->name;
                    $datas['ordered'][$i]['value']=$counted;
                   
                }
                break;

                case 'dfea':
                    $departDistricts=[];
                    $departIds=[];
                    $i=0;
                    $departs=Department::all();
                    foreach ($departs as $depart) {
                        foreach ($depart->municipalities as $key) {
                            foreach ($key->districts as $d) {
                                $departDistricts[$i]= $d;
                                $departIds[$i]= $depart->id;
                                $i++;
                             }
                           }
                    }
                     
                    for ($i=0; $i <count( $departDistricts); $i++) { 
                        $counted=Requete::where('district_id',$departDistricts[$i]->id)->where('service_id',$service_id)->count();
                        $datas['data']['label']="Capes inscrits";
                        $datas['data']['data'][$i]=$counted;
                        $datas['labels'][$i]=$departDistricts[$i]->name;
    
                        $datas['ordered'][$i]['municipality_id']=$departDistricts[$i]->municipality_id;
                        $datas['ordered'][$i]['district_id']=$departDistricts[$i]->id;
                        $datas['ordered'][$i]['label']=$departDistricts[$i]->name;
                        $datas['ordered'][$i]['value']=$counted;
                        $datas['ordered'][$i]['department_id']=$departIds[$i];

                    }
                    break;
        
        default:
        $districts=District::all();
        for ($i=0; $i < $districts->count(); $i++) { 
            $datas['data']['label']="Capes inscrits";
            $datas['data']['data'][$i]=Requete::where('district_id',$districts[$i]->id)->where('service_id',$service_id)->count();
            $datas['labels'][$i]=$districts[$i]->name;
        }
            break;
    }

   



    return $datas;
   }
   public function getForCapeAuthorized($service_id)
   {


    $datas=[];
    $role=Auth::user()->roles()->first()->name;

    switch ($role) {
        case 'cps':
            $districts=Auth::user()->cps->districts;

            for ($i=0; $i < $districts->count(); $i++) { 
                $datas['data']['label']="Capes inscrits";
                $current=$districts[$i];
                $datas['data']['data'][$i]=Cape::whereHas("requete",function($q)use($current,$service_id){
                    $q->where('service_id',$service_id)->where('district_id',$current->id);
                })->count();
                $datas['labels'][$i]=$districts[$i]->name;
            }
            break;
        
        case 'ddasm':
            $departDistricts=[];
            $i=0;
            $depart=Department::find(Auth::user()->department_id);
               foreach ($depart->municipalities as $key) {
                foreach ($key->districts as $d) {
                    $departDistricts[$i]= $d;
                    $i++;
                 }
               }
            for ($i=0; $i < count($departDistricts); $i++) { 
                $datas['data']['label']="Capes autorisés";
                $current=$departDistricts[$i];
                $counted=Cape::whereHas("requete",function($q)use($current,$service_id){
                    $q->where('service_id',$service_id)->where('district_id',$current->id);
                })->count();
                $datas['data']['data'][$i]=$counted;
                $datas['labels'][$i]=$departDistricts[$i]->name;

                $datas['ordered'][$i]['municipality_id']=$departDistricts[$i]->municipality_id;
                $datas['ordered'][$i]['district_id']=$departDistricts[$i]->id;
                $datas['ordered'][$i]['label']=$departDistricts[$i]->name;
                $datas['ordered'][$i]['value']=$counted;
            }
            break;

            case 'dfea':
                $departIds=[];
                $i=0;
                $departs=Department::all();
                foreach ($departs as $depart) {
                    foreach ($depart->municipalities as $key) {
                        foreach ($key->districts as $d) {
                            $departDistricts[$i]= $d;
                            $departIds[$i]= $depart->id;
                            $i++;
                         }
                       }
                }
                 
                for ($i=0; $i < count($departDistricts); $i++) { 
                    $datas['data']['label']="Capes autorisés";
                    $current=$departDistricts[$i];
                    $counted=Cape::whereHas("requete",function($q)use($current,$service_id){
                        $q->where('service_id',$service_id)->where('district_id',$current->id);
                    })->count();
                    $datas['data']['data'][$i]=$counted;
                    $datas['labels'][$i]=$departDistricts[$i]->name;
    
                    $datas['ordered'][$i]['municipality_id']=$departDistricts[$i]->municipality_id;
                    $datas['ordered'][$i]['district_id']=$departDistricts[$i]->id;
                    $datas['ordered'][$i]['label']=$departDistricts[$i]->name;
                    $datas['ordered'][$i]['value']=$counted;
                    $datas['ordered'][$i]['department_id']=$departIds[$i];

                }
                break;
        
        default:
        $districts=District::all();
        for ($i=0; $i < $districts->count(); $i++) { 
            $datas['data']['label']="Capes inscrits";
            $current=$districts[$i];
            $datas['data']['data'][$i]=Cape::whereHas("requete",function($q)use($current,$service_id){
                $q->where('service_id',$service_id)->where('district_id',$current->id);
            })->count();
            $datas['labels'][$i]=$districts[$i]->name;
        }
            break;
    }
  
    return $datas;
   }

   public function getForControl($service_id)
   {
    $datas=[];
    $role=Auth::user()->roles()->first()->name;

    switch ($role) {
        case 'cps':
            $districts=Auth::user()->cps->districts->pluck('id');
            $capes=Cape::with('requete')->whereHas("requete",function($q)use($districts,$service_id){
                $q->where('service_id',$service_id)->whereIn('district_id',$districts);
            })->get();
            for ($i=0; $i < $capes->count(); $i++) { 
                $datas['data'][$i]['count']=$capes[$i]->controls->count();
                $datas['data'][$i]['name']=$capes[$i]->requete?->name;
                
            }
        break;
        case 'ddasm':
            $departDistricts=[];
            $i=0;
            $depart=Department::find(Auth::user()->department_id);
               foreach ($depart->municipalities as $key) {
                foreach ($key->districts as $d) {
                    $departDistricts[$i]= $d->id;
                    $i++;
                 }
               }
            $capes=Cape::with('requete')->whereHas("requete",function($q)use($departDistricts,$service_id){
                $q->where('service_id',$service_id)->whereIn('district_id',$departDistricts);
            })->get();
            for ($i=0; $i < $capes->count(); $i++) { 
                $datas['data'][$i]['count']=$capes[$i]->controls->count();
                $datas['data'][$i]['name']=$capes[$i]->requete?->name;
                
            }
        break;

        default:

            break;

        }


    return $datas;
   }

   
   public function getForCps($agg,$service_id)
   {

    if ($agg == null) {
        $districtIds=Cps::first()->municipality->districts->pluck('id');
    }else {
        $districtIds=Cps::find($agg)->municipality->districts->pluck('id');

    }

    $start_year=2023;
    $end_year=date('Y');
    $datas=[];
    for ($i=0; $i < (($end_year-$start_year)+1); $i++) { 
        $intervalDate[0]=date('01-01-'.strval($start_year)." 00:00:00");
        $intervalDate[1]=date('31-12-'.strval($start_year+$i)." 23:59:59");
        $datas['data'][0]['label']="Capes inscrits";
        $datas['data'][1]['label']="Capes autorisés";
        $datas['data'][0]['data'][$i]=Requete::whereBetween('created_at',$intervalDate)->whereIn('district_id',$districtIds)->where('service_id',$service_id)->count();
        $datas['data'][1]['data'][$i]=Cape::whereBetween('created_at',$intervalDate)->whereHas('requete',function($q)use($districtIds,$service_id){
            $q->where('service_id',$service_id)->whereIn('district_id',$districtIds);
        })->count();
        $datas['labels'][$i]=$start_year+$i;
    }



    return $datas;
   }


   
}
