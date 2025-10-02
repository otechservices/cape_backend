<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityReport;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class ActivityReportController extends Controller
{
    

       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $activity_report=[];

        if (request()->service_id) {

            $service_id=request()->service_id;

        $role=Auth::user()->roles()->first()->name;

        switch ($role) {
            case 'ministre':
                 $activity_report=Cape::with(['ActivityReports.responses','requete.TypeCape'])->whereHas("ActivityReports",function($q){
                    $q->where('is_transmitted',true);
                 })->whereHas('requete',function($q)use($service_id){
                    $q->where('service_id',$service_id);
                })->get();
                break;
            case 'dfea':
                 $activity_report=Cape::with(['ActivityReports.responses','requete.TypeCape'])->whereHas("ActivityReports",function($q){
                    $q->where('is_transmitted',true);
                 })->whereHas('requete',function($q)use($service_id){
                    $q->where('service_id',$service_id);
                })->get();
                break;
            case 'ddasm':
                $districtIds=[];
                $i=0;
                   foreach (Auth::user()->department->municipalities as $key) {
                    foreach ($key->districts as $d) {
                        $districtIds[$i]= $d->id;
                        $i++;
                     }
                   }
        
                 $activity_report=Cape::with(['ActivityReports.responses','requete.TypeCape'])->whereHas("ActivityReports",function($q){
                    $q->where('is_transmitted',true);
                 })->whereHas("requete",function($q)use($districtIds,$service_id){
                    $q->whereIn('district_id',$districtIds)->where('service_id',$service_id);
                 })->get();
                break;
            case 'cps':
                $districtIds=Auth::user()->cps->districts->pluck('id');
                 $activity_report=Cape::withCount(['residents','staffs'])->with(['ActivityReports.responses','requete.TypeCape'])->whereHas("ActivityReports",function($q){
                    $q->where('is_transmitted',true);
                 })->whereHas("requete",function($q)use($districtIds,$service_id){
                    $q->whereIn('district_id',$districtIds)->where('service_id',$service_id);

                 })->get();
                break;
                case 'cape':
                    $activity_report=ActivityReport::where('cape_id',Auth::user()->cape_id)->get();
                   break;
            default:
            $activity_report=[];
                break;
        }
    
    }
        return response()->json([
            "success"=>true,
            "message"=>"Liste des rapport d'activité",
            "data"=>$activity_report
        ],200);

   
    }


    public function getForCape()
    {
        $activity_report=ActivityReport::with(["cape.requete","responses"])->where('cape_id',Auth::user()->cape_id)->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des rapports d'activité",
            "data"=>$activity_report
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
        $code=Auth::user()->cape->requete->code;
        $datas["activity_report_filename"]= FileStorage::setFile("doc_store",$request->file('activity_report_filename'),$code."/reports",time()."-rapport_activité");
        $datas["financial_report_filename"]= FileStorage::setFile("doc_store",$request->file('financial_report_filename'),$code."/reports",time()."-rapport_financier");
        $datas['cape_id']=Auth::user()->cape_id;
        $activity_report=ActivityReport::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un rapport d'activité",
            "data"=>$activity_report
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
        $activity_report=ActivityReport::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un rapport d'activité",
            "data"=>$activity_report
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
       
        $activity_report=ActivityReport::find($id);
      if($request->file('activity_report_filename'))  $datas["activity_report_filename"]= FileStorage::setFile("doc_store",$request->file('activity_report_filename'),$code."/reports",time()."-rapport_activité");
        if($request->file('financial_report_filename'))$datas["financial_report_filename"]= FileStorage::setFile("doc_store",$request->file('financial_report_filename'),$code."/reports",time()."-rapport_financier");

        $activity_report->update($datas);

        $activity_report=ActivityReport::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un rapport d'activité",
            "data"=>$activity_report
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
        $activity_report=ActivityReport::find($id);
        $activity_report->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un rapport d'activité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $data=[];
        $activity_report=ActivityReport::find($id);
        $data['status']=$status;
        if($status==2) $data['is_transmitted']=false;
        $activity_report->update($data);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }
    public function send($id)
    {
        $activity_report=ActivityReport::find($id);
        $activity_report->update(['is_transmitted' =>true]);
        return response()->json([
            "success"=>true,
            "message"=>"Rapport transmis avec succès",
            "data"=>null
        ],200);
    }


    public function getDepartmentWithRelation(Type $var = null)
    {
        $departments=ActivityReport::with(['Municipalities'])->get();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$departments
        ],200);
    }


    public function storeDistricts(Request $request)
    {

        foreach (json_decode($request->items) as $value) {
           District::find($value->id)->update(['cps_id'=>$request->id]);
        }


        return response()->json([
            "success"=>true,
            "message"=>"",
            "data"=>null
        ],200);
       
    }


    public function exportPDF()
    {
        $datas=ActivityReport::all();

        $pdf=Pdf::loadView('pdf.activity_report', [
            "datas"=>$datas,
        ]);

        return $pdf->download('liste_cps.pdf');
    }
}
