<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requete;
use App\Models\Cape;

class SearchController extends Controller
{
    public function index($type)
    {
       $datas=[];
       if (request()->service_id) {
        $service_id=request()->service_id;
       switch ($type) {
        case 'cape-inscrits':
           return $this->getForCape( $service_id);
            break;
        case 'cape-autorises':
           return $this->getForCapeAuthorized( $service_id);
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


    $datas=Requete::with('district.Municipality.Department')->where('service_id',$service_id)->get();
   


    return $datas;
   }
   public function getForCapeAuthorized($service_id)
   {


    $datas=Requete::with("district.Municipality.Department")->where('is_authorized',true)->where('has_agreemant',true)->where('service_id',$service_id)->get();

    return $datas;
   }




}
