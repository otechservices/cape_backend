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
use App\Utilities\FileStorage;
use App\Http\Requests\Session\ForceCloseSessionRequest;
use App\Http\Requests\Session\StoreSessionRequest;
use App\Http\Requests\Session\UpdateSessionRequest;
use App\Services\LogService;
use Spatie\Permission\Models\Role;
use Str,Hash,Auth,DB;


/**
 * Sessions de validation des CAPE.
 *
 * Cycle de vie (cf. Session::isPending/isOpen/isClosed) :
 *  - en préparation (`is_active` NULL) : on la modifie, on y affecte des
 *    membres et on y inscrit des dossiers ;
 *  - ouverte (`is_active` vrai) : les membres donnent leur avis, la DFEA
 *    décide de chaque dossier ;
 *  - close (`is_active` faux) : plus rien ne change, hormis les notes de visite.
 * Une session ne revient jamais en arrière.
 */
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
        $session=Session::with(['sessionMembers.member','requetes'])->orderByDesc('id')->get();
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
     * @param  \App\Http\Requests\Session\StoreSessionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSessionRequest $request)
    {
        $check=Session::whereNull('is_active')->orWhere('is_active',true)->first();
        if ($check) {
            return $this->refuse("La session « ".$check->name." » n'est pas encore close : une seule session peut être en cours à la fois");
        }

        $datas=$request->only(['name','date_start','date_end']);
        $datas['is_active']=null;
        $datas['filename']=FileStorage::setFile("session_store",$request->file('file'),"",time());
        $session=Session::create($datas);

        app(LogService::class)->trace([
            'action_name'=>'Création de session',
            'description'=>"Session #".$session->id." « ".$session->name." »",
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Session créée",
            "data"=>$session
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
     * Le front envoie un multipart en POST avec `_method=PUT` pour pouvoir
     * remplacer la note d'accord.
     *
     * @param  \App\Http\Requests\Session\UpdateSessionRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSessionRequest $request, $id)
    {
        $session=Session::find($id);
        if (!$session) {
            return $this->refuse("Session introuvable",404);
        }
        if (!$session->isPending()) {
            return $this->refuse("Impossible de modifier une session ouverte ou close");
        }

        $datas=$request->only(['name','date_start','date_end']);
        if ($request->file('file')) {
            $datas['filename']=FileStorage::setFile("session_store",$request->file('file'),"",time());
        }
        $session->update($datas);

        app(LogService::class)->trace([
            'action_name'=>'Modification de session',
            'description'=>"Session #".$session->id." « ".$session->name." »",
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Session modifiée",
            "data"=>$session->fresh()
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Seule une session en préparation se supprime : ses dossiers retournent
     * dans la liste « à inscrire », les comptes de ses membres sont supprimés
     * (sinon leurs emails resteraient pris et ils ne pourraient plus être
     * affectés à une autre session).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $session=Session::find($id);
        if (!$session) {
            return $this->refuse("Session introuvable",404);
        }
        if (!$session->isPending()) {
            return $this->refuse("Impossible de supprimer une session ouverte ou close");
        }

        DB::transaction(function () use ($session) {
            foreach ($session->sessionMembers as $value) {
                $value->user?->delete();
                $value->delete();
            }
            foreach ($session->requetes as $value) {
                $value->update(['session_id'=>null]);
                Parcours::create([
                    'libelle'=>"Retrait du dossier de la session « ".$session->name." » (session supprimée)",
                    'requete_id'=>$value->id,
                    'user_id'=>Auth::id(),
                ]);
            }
            $session->delete();
        });

        app(LogService::class)->trace([
            'action_name'=>'Suppression de session',
            'description'=>"Session #".$session->id." « ".$session->name." »",
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Session supprimée",
            "data"=>null
        ],200);
    }

    /**
     * Ouverture (`status` 1) ou clôture normale (`status` 0).
     *
     * Ouvrir exige une session en préparation avec au moins un membre et un
     * dossier ; clôturer exige une session ouverte dont tous les dossiers ont
     * reçu une décision — un rejet (`has_agreemant` à 0) est une décision.
     * Sinon la DFEA dispose de la clôture forcée.
     */
    public function setStatus($id,$status)
    {
        $session=Session::find($id);
        if (!$session) {
            return $this->refuse("Session introuvable",404);
        }
        if (!in_array((string) $status, ['0','1'], true)) {
            return $this->refuse("Statut inconnu");
        }

        if ($status==1) {
            if (!$session->isPending()) {
                return $this->refuse("Seule une session en préparation peut être ouverte");
            }
            if ($session->sessionMembers->count()==0) {
                return $this->refuse("Aucun membre affecté à la session");
            }
            if ($session->requetes->count()==0) {
                return $this->refuse("Aucun dossier inscrit à la session");
            }

            $session->update(['is_active'=>true]);
            $action='Ouverture de session';
            $message="Session ouverte";
        } else {
            if (!$session->isOpen()) {
                return $this->refuse("Seule une session ouverte peut être clôturée");
            }
            $sansDecision=$session->requetes->filter(fn ($r) => $r->has_agreemant === null);
            if ($sansDecision->count()!=0) {
                return $this->refuse(
                    $sansDecision->count()." dossier(s) sans décision : ".$sansDecision->pluck('name')->implode(', ')
                    .". Décidez-les ou utilisez la clôture forcée."
                );
            }

            DB::transaction(function () use ($session) {
                foreach ($session->sessionMembers as $value) {
                    $value->user?->delete();
                }

                $session->update([
                    'is_active'=>false,
                    'closure_mode'=>Session::CLOSURE_NORMAL,
                    'closed_by'=>Auth::id(),
                    'closed_at'=>now(),
                ]);
            });
            $action='Clôture de session';
            $message="Session clôturée";
        }

        app(LogService::class)->trace([
            'action_name'=>$action,
            'description'=>"Session #".$session->id." « ".$session->name." »",
        ]);

        return response()->json([
            "success"=>true,
            "message"=>$message,
            "data"=>null
        ],200);
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
        if (!$session || !$session->isOpen()) {
            return $this->refuse("Seule une session ouverte peut être clôturée de force");
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

    /**
     * Affecte des membres à une session non close et leur crée un compte.
     *
     * `items` : JSON d'identifiants de membres. Les membres déjà affectés sont
     * ignorés. Chaque membre est traité dans sa propre transaction, envoi du
     * mail compris : si le mail part en erreur, aucun compte n'est créé avec un
     * mot de passe que le membre ne recevrait jamais.
     */
    public function storeMembers(Request $request)
    {
        $session=Session::find($request->id);
        if (!$session) {
            return $this->refuse("Session introuvable",404);
        }
        if ($session->isClosed()) {
            return $this->refuse("Impossible d'affecter des membres à une session close");
        }

        $ids=collect(json_decode($request->items) ?? [])->unique()
            ->diff($session->sessionMembers->pluck('member_id'));
        $members=Member::whereIn('id',$ids)->get();
        if ($members->isEmpty()) {
            return $this->refuse("Aucun nouveau membre à affecter");
        }

        $pris=$members->filter(fn ($m) => User::where('email',$m->email)->exists());
        if ($pris->count()!=0) {
            return $this->refuse(
                "Email déjà utilisé pour un compte : ".$pris->map(fn ($m) => $m->firstname." ".$m->lastname." (".$m->email.")")->implode(', ')
                .". Corrigez l'email de ce(s) membre(s) avant de l'affecter."
            );
        }

        $role=Role::whereName('member')->first();
        $echecs=[];
        foreach ($members as $member) {
            try {
                DB::transaction(function () use ($session, $member, $role) {
                    $sm=SessionMember::create([
                        'session_id'=>$session->id,
                        'member_id'=>$member->id,
                    ]);
                    $password=Str::random(8);
                    $user=User::create([
                        "code"=>Str::uuid(),
                        "name"=>$member->firstname." ".$member->lastname,
                        "email"=>$member->email,
                        "password"=>Hash::make($password),
                        "session_member_id"=>$sm->id,
                    ]);
                    $user->assignRole($role);

                    Mailer::sendSimple(
                        "emails.member_account",
                        [
                            "member"=>$member,
                            "password"=>$password
                        ],
                        "Invitation à la session de validation des CAPE",
                        $member->firstname." ".$member->lastname,
                        $member->email);
                });
            } catch (\Throwable $th) {
                report($th);
                $echecs[]=$member->firstname." ".$member->lastname;
            }
        }

        $ajoutes=$members->count()-count($echecs);
        app(LogService::class)->trace([
            'action_name'=>'Affectation de membres',
            'description'=>"Session #".$session->id." « ".$session->name." » : ".$ajoutes." membre(s) affecté(s)",
        ]);

        if (count($echecs)!=0) {
            return $this->refuse(
                $ajoutes." membre(s) affecté(s). Échec de l'invitation (mail non envoyé, membre non affecté) pour : ".implode(', ',$echecs)
            );
        }

        return response()->json([
            "success"=>true,
            "message"=>$ajoutes." membre(s) affecté(s), invitation envoyée",
            "data"=>null
        ],200);
    }

    /**
     * Retire un membre d'une session non close. Ses avis sont supprimés avec lui.
     */
    public function removeMember($id)
    {
        $sm=SessionMember::with('session')->find($id);
        if (!$sm) {
            return $this->refuse("Membre introuvable dans la session",404);
        }
        if ($sm->session?->isClosed()) {
            return $this->refuse("Impossible de retirer un membre d'une session close");
        }

        DB::transaction(function () use ($sm) {
            $sm->avis()->delete();
            $sm->user?->delete();
            $sm->delete();
        });

        app(LogService::class)->trace([
            'action_name'=>'Retrait de membre',
            'description'=>"Session #".$sm->session_id." : membre #".$sm->member_id." retiré",
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Membre retiré de la session",
            "data"=>null
        ],200);
    }

    /**
     * Retire un dossier d'une session non close, tant qu'il n'a pas de décision.
     */
    public function removeRequete($id)
    {
        $requete=Requete::with('session')->find($id);
        if (!$requete || !$requete->session) {
            return $this->refuse("Dossier introuvable dans une session",404);
        }
        if ($requete->session->isClosed()) {
            return $this->refuse("Impossible de retirer un dossier d'une session close");
        }
        if ($requete->has_agreemant !== null) {
            return $this->refuse("Ce dossier a déjà reçu une décision de la session");
        }

        $session=$requete->session;
        $requete->update(["session_id"=>null]);
        Parcours::create([
            'libelle'=>"Retrait du dossier de la session « ".$session->name." »",
            'requete_id'=>$requete->id,
            'user_id'=>Auth::id(),
        ]);

        app(LogService::class)->trace([
            'action_name'=>'Retrait de dossier',
            'description'=>"Session #".$session->id." : dossier ".$requete->code." retiré",
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Dossier retiré de la session",
            "data"=>null
        ],200);
    }

    /**
     * Inscrit des dossiers admissibles (statut 7, sans session) à une session
     * non close. `items` : JSON d'identifiants de dossiers.
     */
    public function storeRequetes(Request $request)
    {
        $session=Session::find($request->id);
        if (!$session) {
            return $this->refuse("Session introuvable",404);
        }
        if ($session->isClosed()) {
            return $this->refuse("Impossible d'inscrire des dossiers à une session close");
        }

        $requetes=Requete::whereIn('id',json_decode($request->items) ?? [])
            ->where('status',7)->whereNull('session_id')->get();
        if ($requetes->isEmpty()) {
            return $this->refuse("Aucun dossier admissible à inscrire");
        }

        DB::transaction(function () use ($session, $requetes) {
            foreach ($requetes as $requete) {
                $requete->update(['session_id'=>$session->id]);
                Parcours::create([
                    'libelle'=>"Inscription du dossier à la session « ".$session->name." »",
                    'requete_id'=>$requete->id,
                    'user_id'=>Auth::id(),
                ]);
            }
        });

        app(LogService::class)->trace([
            'action_name'=>'Inscription de dossiers',
            'description'=>"Session #".$session->id." « ".$session->name." » : ".$requetes->count()." dossier(s)",
        ]);

        return response()->json([
            "success"=>true,
            "message"=>$requetes->count()." dossier(s) inscrit(s)",
            "data"=>null
        ],200);
    }


    public function getSessionRequests()
    {
        $session=Auth::user()->sm?->session;
        if (!$session) {
            return $this->refuse("Vous n'êtes membre d'aucune session",403);
        }

        return response()->json([
            "success"=>true,
            "message"=>"",
            "data"=>$session->requetes
        ],200);

    }

    public function setStatutMember($id,$state)
    {
        $session_member=SessionMember::with('session')->find($id);
        if (!$session_member) {
            return $this->refuse("Membre introuvable dans la session",404);
        }
        if ($session_member->session?->isClosed()) {
            return $this->refuse("Impossible de modifier un membre d'une session close");
        }

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
            "message"=>"Notes de visite enregistrées",
            "data"=>null
        ],200);
    }

    /** Refus métier, au format attendu par le front (qui affiche `message`). */
    private function refuse($message, $code = 422)
    {
        return response()->json([
            "success"=>false,
            "message"=>$message,
            "data"=>null
        ],$code);
    }
}
