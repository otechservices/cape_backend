<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TypeActuality;
use App\Models\Actuality;

use App\Utilities\FileStorage;

use Auth,Str;

class ActualityController extends Controller
{

    public function __construct() {
      
        $this->middleware('auth', ['except' => ['index2','show']]);
    }


       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $actualities=Actuality::all();
      
        return response()->json([
            "success"=>true,
            "message"=>"Création d'actualité",
            "data"=>$actualities
        ],200);
    }
       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index2()
    {
        $actualities=Actuality::all();
      
        return response()->json([
            "success"=>true,
            "message"=>"Création d'actualité",
            "data"=>$actualities
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
        $datas=$request->all();
        if ($request->file('big_photo')) {
            $filename=FileStorage::setFile('public',$request->file('big_photo'),'actualities', Str::slug($request->title).time());
            $datas['big_photo']=$filename;
        }
        if ($request->file('short_photo')) {
            $filename=FileStorage::setFile('public',$request->file('short_photo'),'actualities', Str::slug($request->title).time());
            $datas['short_photo']=$filename;
        }
        $datas['user_id']=Auth::id();
        $actuality=Actuality::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Création d'actualité",
            "data"=>null
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

        $actuality=Actuality::find($id);
        if ($request->file('big_photo')) {
            FileStorage::deleteFile('public', $actuality->big_photo,'actualities');
            $filename=FileStorage::setFile('public',$request->file('big_photo'),'actualities', Str::slug($request->title).time());
            $datas['big_photo']=$filename;
        }
        if ($request->file('short_photo')) {
            FileStorage::deleteFile('public', $actuality->short_photo,'actualities');
            $filename=FileStorage::setFile('public',$request->file('short_photo'),'actualities', Str::slug($request->title).time());
            $datas['short_photo']=$filename;
        }
        $actuality->update($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Mise à jour d'actualité",
            "data"=>null
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

        $actuality=Actuality::find($id);

        if ( $actuality) {
            $actuality->delete();
            return response()->json([
                "success"=>true,
                "message"=>"Création d'actualité",
                "data"=>null
            ],200);
        
        }else {

            return response()->json([
                "success"=>true,
                "message"=>"La ressource que vous essayez de retirer n'existe pas",
                "data"=>null
            ],500);
        }
    }


    public function setPublishState($id,$state)
    {

        $actuality=Actuality::find($id);
        
        $actuality->update(['is_active'=>$state]);

        if ( $state) {
            return response()->json([
                "success"=>true,
                "message"=>"Un élément publié avec succès",
                "data"=>null
            ],200);
        }else {
            return response()->json([
                "success"=>true,
                "message"=>"Un élément non publié avec succès",
                "data"=>null
            ],200);
        }
    }


    public function show($id)
    {
        $actuality=Actuality::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un avis",
            "data"=>$actuality
        ],200);
    }

}
