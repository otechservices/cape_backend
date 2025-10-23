<?php

namespace App\Http\Controllers;
use App\Models\Session;
use App\Models\Requete;
use App\Models\SessionMember;
use App\Models\Member;
use App\Models\Parcours;
use Illuminate\Http\Request;
use App\Utilities\Mailer;
use App\Models\User;
use App\Models\Cape;
use App\Utilities\FileStorage;
use Spatie\Permission\Models\Role;
use Str,Hash,Auth;


class SessionController extends Controller
{

    public function __construct() {
      
        $this->middleware('auth', ['except' => ['results']]);
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $session=Session::with(['sessionMembers.member','requetes'])->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des sessions",
            "data"=>$session
        ],200);
    }

       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function results()
    {
        $session=Session::withCount(['requetes','requetesOk','requetesNok'])->where("is_active",false)->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des sessions",
            "data"=>$session
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

        $check=Session::where('is_active',null)->orWhere('is_active',true)->first();
        if ($check) {
            return response()->json([
                "success"=>true,
                "message"=>"Vous avez déjà une session en cours",
                "data"=>null
            ],500);
        }else{
            $datas = $request->all();
            $datas['is_active']=null;
            if ($request->file('file')) {
                $filename= FileStorage::setFile("session_store",$request->file('file'),"",time());
                $datas['filename']=$filename;
                unset( $datas['file']);
            }
            $session=Session::create($datas);
    
            return response()->json([
                "success"=>true,
                "message"=>"Enregistrement d'une session",
                "data"=>$session
            ],200);
        }
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $session=Session::find($id);
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'une session",
            "data"=>$session
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
       
        $session=Session::find($id);
        if ($session->is_active != null) {
            return response()->json([
                "success"=>false,
                "message"=>"Impossible de modifier une session active ou clôturée",
                "data"=>null
            ],500);
        
        }

        $session->update($datas);

        $session=Session::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$session
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
        $session=Session::find($id);
        if ( $session->is_active != null) {
            return response()->json([
                "success"=>false,
                "message"=>"Impossible de modifier une session active ou clôturée",
                "data"=>null
            ],200);
        }
        if ($session->sessionMembers->count()!=0) {
            foreach ($session->sessionMembers as $value) {
                $value->delete();
            }
        }
        if ($session->requetes->count()!=0) {
            foreach ($session->requetes as $value) {
                $value->update(['session_id'=>null]);
            }
        }
        
        $session->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une session",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        
        $session=Session::find($id);

        if ($session->sessionMembers->count()==0) {
            return response()->json([
                "success"=>false,
                "message"=>"Aucun membre ajouté",
                "data"=>null
            ],500);
        }elseif ($session->requetes->count()==0) {
            return response()->json([
                "success"=>false,
                "message"=>"Aucun dossier inscrits",
                "data"=>null
            ],500);
        }else {
            if ($status==1) {
                $session->update(['is_active' =>$status]);
       
            }else {
                foreach ($session->requetes as $value) {
                    if ($value->has_agreemant == null) {
                        return response()->json([
                            "success"=>false,
                            "message"=>"Tous les dossiers n'ont pas été clôturés",
                            "data"=>null
                        ],500);
                    }
                }


                    foreach ($session->sessionMembers as $value) {
                       $value->user?->delete();
                    }

                $session->update(['is_active' =>$status]);
              
            }

            return response()->json([
                "success"=>true,
                "message"=>"Status mis à jour avec succès",
                "data"=>null
            ],200);
      
        }
       
       
    }




    
    public function storeMembers(Request $request)
    {

        foreach (json_decode($request->items) as $value) {
            $member=Member::find($value);

            $check= User::where('email',$member->email)->first();

            if ($check != null) {
                return response()->json([
                    "success"=>true,
                    "message"=>"Cet email ".$member->email." est déjà utilisé pour un compte, veuillez utiliser un autre email",
                    "data"=>null
                ],500);
            }
        }
        foreach (json_decode($request->items) as $value) {
            $check=SessionMember::where("session_id",$request->id)->where('member_id',$value)->first();
            if ($check == null) {
                $sm= SessionMember::create([
                    'session_id'=>$request->id,
                    'member_id'=>$value,
                ]);
                $member=Member::find($value);
                $password=Str::random(8);
                $datas["code"]=Str::uuid();
                $datas["name"]=$member->firstname." ".$member->lastname;
                $datas["email"]=$member->email;
                $datas["password"]=Hash::make($password);
                $datas["session_member_id"]= $sm->id;
                $user=User::create($datas);
                $user->assignRole(Role::whereName('member')->first());
        
                Mailer::sendSimple(
                    "emails.member_account",
                    [
                        "member"=>$member,
                        "password"=>$password
                    ],
                    "Invitation à la session de validation des CAPE",
                    $request->name_promoter,
                    $member->email);
        
            }

  
        }


        return response()->json([
            "success"=>true,
            "message"=>"Membre ajouter avec succès",
            "data"=>null
        ],200);
       
    }
    public function removeMember($id)
    {

       $sm= SessionMember::find($id);
       if ($sm) {
        if ($sm->avis->count() != 0) {
            foreach ($sm->avis as  $value) {
                $value->delete();
            }
            if ($sm->user) {
                $sm->user->delete();
            }
            $sm->delete();
            // return response()->json([
            //     "success"=>true,
            //     "message"=>"Vous ne pouvez pas retirer ce membre. Il a déjà donné des avis",
            //     "data"=>null
            // ],500);
        } else {
            if ($sm->user) {
                $sm->user->delete();
            }
            $sm->delete();
        }
        
       
       }
        return response()->json([
            "success"=>true,
            "message"=>"Un membre retiré avec succès",
            "data"=>null
        ],200);
       
    }
    public function removeRequete($id)
    {

        Requete::find($id)->update(["session_id"=>null]);
        return response()->json([
            "success"=>true,
            "message"=>"Un dossier retiré avec succès",
            "data"=>null
        ],200);
       
    }
    public function storeRequetes(Request $request)
    {

        foreach (json_decode($request->items) as $value) {
           Requete::find($value)->update([
            'session_id'=>$request->id
        ]);


        Parcours::create([
            'libelle'=>"Inscription du dossier à la session",
            'requete_id'=>$value,
            'user_id'=>Auth::id(),
        ]); 
        }


        return response()->json([
            "success"=>true,
            "message"=>"Dossiers inscrits",
            "data"=>null
        ],200);
       
    }


    public function getSessionRequests()
    {
        $requests= Auth::user()->sm->session->requetes;
        return response()->json([
            "success"=>true,
            "message"=>"",
            "data"=>$requests
        ],200);
    
    }

    public function setStatutMember($id,$state)
    {
        $session_member=SessionMember::find($id);    
        $session_member->update(['is_active' =>$state]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    
    }

    function enregistrerNotes(Request $request) {

        $request->validate([
            'data' => 'required|array',
            'data.*.id' => 'required|exists:requetes,id',
            'data.*.note_terrain' => 'required|numeric|min:0|max:100',
            'data.*.note_globale' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->data as $item) {
            Requete::where('id', $item['id'])->update([
                'note_terrain' => $item['note_terrain'],
                'note_globale' => $item['note_globale'],
            ]);
        }

        
         return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }
}

