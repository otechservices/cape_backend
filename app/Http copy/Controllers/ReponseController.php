<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth,Mail,PDF,Http,Hash,Str;
use App\Models\Requete;
use App\Models\Prestation;
use App\Models\Reponse;
use App\Utilities\Core;
use App\Utilities\FileStorage;
use Illuminate\Support\Facades\Storage;
use App\Models\Parcours;
use App\Utilities\Mailer;


/** status check
 * 0 : Nouvelle
 * 1 : Mise en attente
 * 2 : Rejeté
 * 3 : Corrigé
 * 4 : Validé
 * 5 : Finalisé
 * 6 : Visa-DDASM
 * 7 : A inscrire
 */

class ReponseController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Reponse
     */
    public function index()
    {
        //
    }

    public function needCorrection(Request $request)
    {
        $req=Requete::whereId($request->id)->first();

        Reponse::create([
            // 'hasPermission'=>$request->hasPermission,
            // 'reason'=>$request->reason,
            'observation'=>$request->observation,
            'requete_id'=>$request->id,
            'user_id'=>Auth::id()
        ]);

        Parcours::create(['libelle'=>"Demande mise en attente pour complément d'information.Motif: ".$request->observation,'requete_id'=>$request->id,'user_id'=>Auth::id()]);

        $token=Str::random(60);
        Mailer::sendSimple(
            "emails.update",
            [
                'code'=>$req->code,
                'token'=>$token,
                'motif'=>$request->observation
            ],
            "Avis sur demande d'autorisation ".$req->service?->name,
            $req->name_promoter,
            $req->email);

            $req->update(["status"=>1,"token"=> $token]);

        return response()-> json(["status" => true, "message" => ""],200);

    }



    public function decline(Request $request){

        $req=Requete::whereId($request->id)->first();

        Reponse::create([
            'hasPermission'=>$request->hasPermission,
            'reason'=>$request->reason,
            'observation'=>$request->observation,
            'requete_id'=>$request->id,
            'user_id'=>Auth::id()
        ]);

        Parcours::create(['libelle'=>"Demande rejetée",'requete_id'=>$request->id,'user_id'=>Auth::id()]);

        Mailer::sendSimple(
            "emails.rejected",
            ['motif'=>$request->observation],
            "Avis sur demande d'autorisation ".$req->service?->name,
            $req->name_promoter,
            $req->email);

            $req->update(["status"=>2]);

        return response()-> json(["status" => true, "message" => ""],200);
    }

    public function validation(Request $request){

        $req=Requete::whereId($request->id)->first();

        Reponse::create([
            'hasPermission'=>$request->hasPermission,
            'reason'=>$request->reason,
            'observation'=>$request->observation,
            'requete_id'=>$request->id,
            'user_id'=>Auth::id()
        ]);

        Parcours::create(['libelle'=>"Demande rejetée",'requete_id'=>$request->id,'user_id'=>Auth::id()]);

        Mailer::sendSimple(
            "emails.success",
            ['code'=>$req->code,'motif'=>$request->observation],
            "Validation demande d'autorisation ".$req->service?->name,
            $req->name_promoter,
            $req->email);

            $req->update(["status"=>4]);
            return response()-> json(["status" => true, "message" => ""],200);

    }


  
    
}
