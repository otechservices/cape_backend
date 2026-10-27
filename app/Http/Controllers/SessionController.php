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
use App\Http\Requests\Session\ForceCloseSessionRequest;
use App\Services\LogService;
use Spatie\Permission\Models\Role;
use Str,Hash,Auth,DB;


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
        $session=Session::withCount(['requetes','requetesOk','requetesNok'])->where("is_active",false)->notCancelled()->get();
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

                $session->update([
                    'is_active' =>$status,
                    'closure_mode' => Session::CLOSURE_NORMAL,
                    'closed_by' => Auth::id(),
                    'closed_at' => now(),
                ]);

            }

            return response()->json([
                "success"=>true,
                "message"=>"Status mis à jour avec succès",
                "data"=>null
            ],200);

        }


    }

    /**
     * Clôture forcée d'une session ouverte, réservée à la DFEA.
     *
     * Sert quand la clôture normale est bloquée parce que des dossiers n'ont pas
     * reçu de décision. Selon `mode` :
     *  - `continue` : les dossiers déjà décidés restent dans la session et
     *    poursuivent vers l'autorisation ; les autres sont retirés de la session
     *    et reviennent dans la liste « à inscrire en session » ;
     *  - `cancel` : la session est annulée. Ses décisions, observations, notes et
     *    recommandations sont effacées, tous ses dossiers reviennent dans la
     *    liste. Les avis restent en base mais ne sont plus lus (Requete::avis).
     *    Les mails de rejet déjà envoyés, eux, ne peuvent pas être rappelés.
     * Dans les deux cas les comptes des membres sont supprimés, comme à la
     * clôture normale.
     */
    public function forceClose(ForceCloseSessionRequest $request, $id)
    {
        if (!in_array(Auth::user()->roles()->first()?->name, ['dfea', 'admin'])) {
            return response()->json([
                "success"=>false,
                "message"=>"Seule la DFEA peut forcer la clôture d'une session",
                "data"=>null
            ],403);
        }

        $session=Session::find($id);
        if (!$session || $session->is_active != true) {
            return response()->json([
                "success"=>false,
                "message"=>"Seule une session ouverte peut être clôturée de force",
                "data"=>null
            ],500);
        }

        $cancel = $request->mode === 'cancel';
        $reason = trim($request->reason);
        $retires = 0;

        DB::transaction(function () use ($session, $cancel, $reason, &$retires) {
            foreach ($session->requetes as $requete) {
                if ($cancel) {
                    // Une recommandation déjà suivie de contrôles n'est plus une simple
                    // délibération : on la garde.
                    $requete->referals()->whereDoesntHave('controls')->delete();
                    $requete->update([
                        'session_id'=>null,
                        'has_agreemant'=>null,
                        'observation'=>null,
                        'note'=>null,
                    ]);
                    $libelle = "Annulation de la session « ".$session->name." », dossier à réinscrire. Motif : ".$reason;
                } elseif ($requete->has_agreemant === null) {
                    $requete->update(['session_id'=>null]);
                    $libelle = "Retrait de la session « ".$session->name." » (clôture forcée sans décision), dossier à réinscrire. Motif : ".$reason;
                } else {
                    continue;
                }

                $retires++;
                Parcours::create([
                    'libelle'=>$libelle,
                    'requete_id'=>$requete->id,
                    'user_id'=>Auth::id(),
                ]);
            }

            foreach ($session->sessionMembers as $value) {
                $value->user?->delete();
            }

            $session->update([
                'is_active'=>false,
                'closure_mode'=>$cancel ? Session::CLOSURE_FORCED_CANCEL : Session::CLOSURE_FORCED_CONTINUE,
                'closure_reason'=>$reason,
                'closed_by'=>Auth::id(),
                'closed_at'=>now(),
            ]);
        });

        app(LogService::class)->trace([
            'action_name'=>$cancel ? 'Annulation de session' : 'Clôture forcée de session',
            'description'=>"Session #".$session->id." « ".$session->name." » : ".$retires." dossier(s) remis à inscrire. Motif : ".$reason,
        ]);

        return response()->json([
            "success"=>true,
            "message"=>$cancel
                ? "Session annulée : ".$retires." dossier(s) à réinscrire"
                : "Session clôturée : ".$retires." dossier(s) sans décision remis à inscrire",
            "data"=>null
        ],200);
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

      // Validation du payload
        $request->validate([
            '*.id' => 'required|exists:requetes,id',
            '*.note_terrain' => 'required|numeric|min:0|max:100',
            '*.note_finale' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->all() as $item) {
            Requete::where('id', $item['id'])->update([
                'note_terrain' => $item['note_terrain'],
                'note_finale' => $item['note_finale'],
            ]);
        }

         return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }
}

