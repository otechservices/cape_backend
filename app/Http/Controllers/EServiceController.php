<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Requete;
use App\Models\Parcours;
use App\Models\RequeteFile;
use App\Utilities\FileStorage;
use VIPSoft\Unzip\Unzip;
use App\Models\RequeteTypeGarderie;
use Str,Mail,PDF,File,Artisan,Http,ZipArchive,Storage,Validator,Auth;


class EServiceController extends Controller
{



            
            public function store(Request $request)
            {
                //try {
       
                $code=isset(json_decode($request->data)->code)? json_decode($request->data)->code:null;

                if ($code==null) {
                    return RequeteController::store($request);
                } else {
                    return RequeteController::update($request);
                }
                

                    return response()->json([
                                "success"=>true,
                                "message"=>"Echec de soumission",
                                "data"=>null
                            ],500);

                // } catch (\Illuminate\Database\QueryException $ex) {
                //     info("Database: ".$ex->getMessage());
                //     return response()->json([
                //     "success"=>false,
                //     "message"=>"Echec de soumission",
                //     "data"=>null
                // ],500);
                // } catch (\Exception $e) {
                //     info("Error: ".$e->getMessage());
                //     return response()->json([
                //         "success"=>false,
                //         "message"=>"Un problème est survenu! Veuillez contacter l'administrateur ou le support",
                //         "data"=>null
                //     ],500);
        
                // }
            }

   



            
            function getOne($token,$code)
            {
              $check= Requete::whereToken($token)->whereCode($code)->first();

              if ($check) {
                $requetes=Requete::with(['files.file.TypeFile','reponses','parcours','affectation','TypeCape','service','RequeteTypeGarderies.TypeGarderie','district.Municipality.Department'])->where('code',$code)->first();
                return response()->json([
                    "success"=>true,
                    "message"=>"",
                    "data"=>$requetes
                ],200);
              }else {
                return response()->json([
                    "success"=>true,
                    "message"=>"Ressource non trouvée",
                    "data"=>null
                ],500);
              }
            }



    public static function sendSubmittedMail($name,$email,$subject,$data,$file)
    {

        Mail::send($file, $data, function($message)use ($name,$email,$subject){
            $message->from(env('MAIL_FROM_ADDRESS'), env("MAIL_FROM_NAME"))
                ->subject($subject);
            $message->to($email, $name)
            ;
        });

      
        return ;
    }



   

    function addFile(Request $request)  {

            $filename= FileStorage::setFile("doc_store",$request->file,$request->init_code,$request->name.Str::random());
            RequeteFile::create([
                "type"=>"PDF",
                "reference"=>$request->reference,
                "filename"=>$filename,
                "level"=>0,
                "file_id"=>$request->file_id,
                "promoter_id"=>Auth::user()->promoter_id,
                "init_code"=>$request->init_code,
            ]);

            return response()->json([
                "success"=>true,
                "message"=>"Fichier ajouté",
                "data"=>null
            ],200);
    }

    function purgeFile(Request $request){
       $files= RequeteFile::where('init_code',$request->init_code)->where('requete_id',null)->get();

       foreach ($files as $key => $value) {
        $value->delete();
       }

       return response()->json([
        "success"=>true,
        "message"=>"Fichier ajouté",
        "data"=>null
    ],200);
    }
}


