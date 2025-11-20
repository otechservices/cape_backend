<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Mail\ContactFormMail;
use Mail;

class MessageController extends Controller
{


    public function __construct() {
      
        $this->middleware('auth', ['except' => ['store','index']]);
    }


      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $file=Message::all();
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

        unset($datas['conditions']);
        
        $file=Message::create($datas);

        $to=env('MAIL_FROM_ADDRESS');
        Mail::to($to)->send(new ContactFormMail($datas));

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un message",
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
        $file=Message::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un message",
            "data"=>$file
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
       
        $file=Message::find($id);
        unset($datas['conditions']);

        $file->update($datas);

        $file=Message::find($id);

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
        $file=Message::find($id);
        $file->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un message",
            "data"=>null
        ],200);
    }
}
