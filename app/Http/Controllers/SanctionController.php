<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sanction;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;


class SanctionController extends Controller
{
      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $section=Sanction::with(['cape.requete','TypeSanction'])->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des sanctions",
            "data"=>$section
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
        
        if ($request->file('file')) {
            $datas["decret_filename"]= FileStorage::setFile("doc_store",$request->file('file'),Cape::find($request->cape_id)->requete->code,time());
            unset($datas['file']);
        }
        $section=Sanction::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une sanction",
            "data"=>$section
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
        $section=Sanction::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une sanction",
            "data"=>$section
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
        if ($request->file('file')) {
            $datas["decret_filename"]= FileStorage::setFile("doc_store",$request->file('file'),Cape::find($request->cape_id)->requete->code,time());

            unset($datas['file']);
        }
        $section=Sanction::find($id);

        $section->update($datas);

        $section=Sanction::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une sanction",
            "data"=>$section
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
        $section=Sanction::find($id);
        $section->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une sanction",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $section=Sanction::find($id);
        $section->update(['status' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }

}
