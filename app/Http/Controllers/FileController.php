<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Service;
use App\Models\RequeteFile;
use Auth;

class FileController extends Controller
{

    public function __construct() {
      
        $this->middleware('auth', ['except' => ['index','show','store']]);
    }
      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $checkService=Service::where('name','like','%'.request()->type.'%')->first();

        if ( $checkService) {
            $file=File::with("TypeFile")->where('service_id',$checkService->id)->where('is_published',true)->get();
        }else{
           // if(Auth::user()!= null){
                $file=File::with(["TypeFile",'type'])->get();

            // }else{
            //     $file=[];
            // }
        }
       
        return response()->json([
            "success"=>true,
            "message"=>"Liste des périodicités",
            "data"=>$file
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
        
        $file=File::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une périodicité",
            "data"=>$file
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
        $check=RequeteFile::with(['deliver','requete'])->where('token',$id)->first();

        if ($check) {
            return response()->json([
                "success"=>true,
                "message"=>"Document inexistant",
                "data"=>$check
            ],200);
        } else {
            return response()->json([
                "success"=>true,
                "message"=>"Document inexistant",
                "data"=>null
            ],500);
        }
        
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
       
        $file=File::find($id);

        $file->update($datas);

        $file=File::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$file
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
        $file=File::find($id);
        $file->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une périodicité",
            "data"=>null
        ],200);
    }

    public function check($token)
    {
        $check=RequeteFile::with(['devliver','requete'])->where('token',$token)->first();

        if ($token) {
            return response()->json([
                "success"=>true,
                "message"=>"Document inexistant",
                "data"=>$token
            ],200);
        } else {
            return response()->json([
                "success"=>true,
                "message"=>"Document inexistant",
                "data"=>null
            ],500);
        }
        
    }
}
