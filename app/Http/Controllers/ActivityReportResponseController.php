<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityReportResponse;


class ActivityReportResponseController extends Controller
{
     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datas = $request->all();
        
        $activity_report_response=ActivityReportResponse::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une réponse de rapport d'activité",
            "data"=>$activity_report_response
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
        $activity_report_response=ActivityReportResponse::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une réponse de rapport d'activité",
            "data"=>$activity_report_response
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
       
        $activity_report_response=ActivityReportResponse::find($id);

        $activity_report_response->update($datas);

        $activity_report_response=ActivityReportResponse::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$activity_report_response
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
        $activity_report_response=ActivityReportResponse::find($id);
        $activity_report_response->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une réponse de rapport d'activité",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $activity_report_response=ActivityReportResponse::find($id);
        $activity_report_response->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }

}
