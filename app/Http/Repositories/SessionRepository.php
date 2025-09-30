<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Session;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class SessionRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Session
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Session::class);
    }

    /**
     * Vérifie si la fête existe.
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Récupère toutes les fêtes avec pagination et filtres.
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Session::with(['sessionMembers.member', 'requetes'])
            ->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    public function results()
    {
        $session = Session::withCount(['requetes','requetesOk','requetesNok'])
            ->where("is_active", false)
            ->get();
        return $session;
    }


    public function show($id)
    {
        $session = Session::find($id);
        return $session;
    }

    /**
     * Récupère une fête spécifique.
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Crée une nouvelle fête.
     */
    public function makeStore(Request $request): Session
    {
        $check = Session::where('is_active', null)
            ->orWhere('is_active', true)
            ->first();

        if ($check) {
            // On retourne juste un message ou une exception si tu ne veux plus de JSON
            throw new \Exception("Vous avez déjà une session en cours");
        } else {
            $data = $request->all();
            $data['is_active'] = null;

            if ($request->file('file')) {
                $filename = FileStorage::setFile(
                    "session_store",
                    $request->file('file'),
                    "",
                    time()
                );
                $data['filename'] = $filename;
                unset($data['file']);
            }

            $model = new Session($data);
            $model->save();

            return $model;
        }
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): Session
    {
        $data = $request->all();
        $session = Session::findOrFail($id);

        if ($session->is_active !== null) {
            throw new \Exception("Impossible de modifier une session active ou clôturée");
        }
        $session->update($data);

        return $session;
    }

    /**
     * Supprime une fête.
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Récupère les fêtes les plus récentes.
     */
    public function getlatest()
    {
        return $this->latest()->get();
    }

    /**
     * Modifie le statut d'une fête.
     */
    public function setStatus($id, $status)
    {
        $session = Session::findOrFail($id);

        if ($session->sessionMembers->count() == 0) {
            throw new \Exception("Aucun membre ajouté");
        } elseif ($session->requetes->count() == 0) {
            throw new \Exception("Aucun dossier inscrit");
        } else {
            if ($status == 1) {
                $session->update(['is_active' => $status]);
            } else {
                foreach ($session->requetes as $value) {
                    if ($value->has_agreemant == null) {
                        throw new \Exception("Tous les dossiers n'ont pas été clôturés");
                    }
                }

                foreach ($session->sessionMembers as $value) {
                    $value->user?->delete();
                }

                $session->update(['is_active' => $status]);
            }
        }

        return $session;
    }


    public function storeMembers(Request $request)
    {
        foreach (json_decode($request->items) as $value) {
            $member = Member::findOrFail($value->id);
            $check = User::where('email', $member->email)->first();

            if ($check) {
                throw new \Exception("Cet email {$member->email} est déjà utilisé pour un compte, veuillez utiliser un autre email");
            }
        }

        $createdUsers = [];

        foreach (json_decode($request->items) as $value) {
            $check = SessionMember::where("session_id", $request->id)
                ->where('member_id', $value->id)
                ->first();

            if (!$check) {
                $sm = SessionMember::create([
                    'session_id' => $request->id,
                    'member_id' => $value->id,
                ]);

                $member = Member::findOrFail($value->id);
                $password = Str::random(8);

                $datas = [
                    "code" => Str::uuid(),
                    "name" => $member->firstname . " " . $member->lastname,
                    "email" => $member->email,
                    "password" => Hash::make($password),
                    "session_member_id" => $sm->id,
                ];

                $user = User::create($datas);
                $user->assignRole(Role::whereName('member')->first());
                $createdUsers[] = $user;

                // Envoi du mail
                Mailer::sendSimple(
                    "emails.member_account",
                    ["member" => $member, "password" => $password],
                    "Invitation à la session de validation des CAPE",
                    $request->name_promoter,
                    $member->email
                );
            }
        }

        return $createdUsers;
}


    public function removeMember($id)
    {
        $sm = SessionMember::find($id);

        if (!$sm) {
            throw new \Exception("Le membre n'existe pas");
        }

        if ($sm->avis->count() != 0) {
            foreach ($sm->avis as $avis) {
                $avis->delete();
            }
        }

        if ($sm->user) {
            $sm->user->delete();
        }

        $sm->delete();
        return $sm;
    }


    public function removeRequete($id)
    {
        $requete = Requete::find($id);

        if (!$requete) {
            throw new \Exception("Le dossier n'existe pas");
        }
        $requete->update(['session_id' => null]);
        return $requete;
    }


    public function storeRequetes(Request $request)
    {
        foreach (json_decode($request->items) as $value) {
            $requete = Requete::find($value->id);

            if ($requete) {
                $requete->update([
                    'session_id' => $request->id
                ]);

                Parcours::create([
                    'libelle' => "Inscription du dossier à la session",
                    'requete_id' => $value->id,
                    'user_id' => Auth::id(),
                ]);
            }
        }

        return Requete::whereIn('id', collect(json_decode($request->items))->pluck('id'))->get();
    }


    public function getSessionRequests()
    {
        return Auth::user()->sm->session->requetes;
    }



    public function setStatutMember($id, $state)
    {
        $session_member = SessionMember::find($id);
        if ($session_member) {
            $session_member->update(['is_active' => $state]);
        }

        return $session_member;
    }


}
