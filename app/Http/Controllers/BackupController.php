<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Backup;
use Artisan;

class BackupController extends Controller
{
    public function index()
    {
        $backups=Backup::all();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des sauvegardes",
            "data"=>$backups
        ],200);
    }

    public function store(Request $request)
    {

        try {
            Artisan::call('backup:run');

            Backup::create([
                "name"=>"sauvegarde du ".date('Y-m-d h:i:s')
            ]);
            return response()->json([
                "success"=>true,
                "message"=>"Enregistrement d'une sauvegarde",
                "data"=>null
            ],200);
        
        } catch (Exception $th) {
            return response()->json([
                "success"=>true,
                "message"=>"Erreur de sauvegarde",
                "data"=>null
            ],500);        }

     
    }

}
