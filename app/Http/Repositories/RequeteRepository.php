<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Requete;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class RequeteRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Requete
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Requete::class);
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
        $requetes = collect(); // par défaut, une collection vide

        if (request()->service_id) {
            $role = Auth::user()->roles()->first()->name;

            switch ($role) {
                case 'cps':
                    $districtIds = Auth::user()->cps->districts->pluck('id');
                    $query = Requete::with(['parcours.user', 'TypeCape', 'service', 'lastParcours'])
                        ->whereIn('district_id', $districtIds)
                        ->where('service_id', request()->service_id)
                        ->orderByDesc('id');
                    break;

                case 'ddasm':
                    $districtIds = [];
                    $k = 0;
                    foreach (Auth::user()->department->municipalities as $value) {
                        foreach ($value->districts as $value2) {
                            $districtIds[$k] = $value2->id;
                            $k++;
                        }
                    }
                    $query = Requete::with(['parcours.user', 'TypeCape', 'service', 'district.cps', 'lastParcours'])
                        ->whereIn('district_id', $districtIds)
                        ->where('service_id', request()->service_id)
                        ->orderByDesc('id');
                    break;

                case 'dfea':
                case 'ministre':
                    $query = Requete::with(['parcours.user', 'TypeCape', 'service', 'lastParcours'])
                        ->where('service_id', request()->service_id)
                        ->orderByDesc('id');
                    break;

                default:
            $query = null;
            break;
            }

            if (!is_null($query)) {
                if (array_key_exists('per_page', request()->all())) {
                    $per_page = request('per_page');
                    $requetes = $query->paginate($per_page);
                } else {
                    $requetes = $query->get();
                }
            }
        }else{
          $requetes=   Requete::with(['parcours.user', 'TypeCape', 'service', 'lastParcours'])
                        ->ignoreRequest(['per_page'])
                        ->orderByDesc('id')
                        ->get();
        }
        return $requetes;
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
    public function makeStore($data): Requete
    {
        if ($request->input('codeForRecepisse')) {
            try {
                $requete = Requete::where('code', $request->input('codeForRecepisse'))->first();
                $requeteFile = RequeteFile::where('requete_id', $requete->id)
                    ->where('reference', 'Récépicé inscription')->first();

                $service = Service::find((int) $requete->service_id);

                $filePath = public_path('docs/' . $requete->code . '/' . $requeteFile->filename);
                if (!file_exists($filePath)) {
                    Log::error("Fichier récépissé non trouvé pour la requête: " . $requete->id);
                }

                // Préparer les emails
                $emails = collect([
                    ['email' => $requete->email, 'name' => "A l'attention de " . $requete->name],
                    ['email' => $requete->email_pomoter, 'name' => 'Cher ' . $requete->name_pomoter . ' ' . $requete->firstname_pomoter],
                    ['email' => $requete->email_chief, 'name' => 'Cher ' . $requete->name_chief . ' ' . $requete->firstname_chief],
                ])->unique('email')->values()->all();

                foreach ($emails as $item) {
                    $mailResult = Mailer::sendSimpleWithFile(
                        "emails.submit_success",
                        ["cape" => $requete, "name" => $item['name'], 'code' => $requete->code, 'service' => $service, 'cps' => $requete->district->cps->name],
                        "Soumission de dossier CAPE/Garderie",
                        $item['name'],
                        $item['email'],
                        [$filePath]
                    );
                    if (!$mailResult['success']) {
                        Log::error('Échec d\'envoi à ' . $item['email']);
                    }
                }

                return $requete; // On retourne le modèle créé/trouvé

            } catch (\Exception $th) {
                Log::error($th->getMessage());
                return null;
            }

        } else {
            $data = json_decode($request->data);
            $district = District::find($data->district_id);
            $user = User::where('cps_id', $district->cps?->id)->first();
            $service = Service::find((int)$request->service_id);
            $code = RequeteController::generateUniqueCode($service);

            $dataR = [];
            if ($data->has_aggrement) {
                $dataR['aggreement_reference'] = $data->aggreement_reference;
                $dataR['aggreement_year'] = $data->aggreement_year;
                $dataR['file_aggreement'] = FileStorage::setFile("doc_store", $request->file('file_aggreement'), $code, time());
                $dataR['has_agreemant'] = true;
                $dataR['is_authorized'] = true;
            }

            $filename = $request->file('zone_file') ? FileStorage::setFile("doc_store", $request->file('zone_file'), $code, time()) : null;
            $filename2 = $request->file('registered_proof') ? FileStorage::setFile("doc_store", $request->file('registered_proof'), $code, time()) : null;
            $consent_file = $request->file('consent_file') ? FileStorage::setFile("doc_store", $request->file('consent_file'), $code, time()) : null;

            $requeteData = array_merge([
                "code" => $code,
                "name" => $data->name,
                "type_cape_id" => $data->type_cape_id ?? null,
                "name_pomoter" => $data->name_pomoter,
                "firstname_pomoter" => $data->firstname_pomoter ?? null,
                "phone_pomoter" => $data->phone_pomoter ?? null,
                "email_pomoter" => $data->email_pomoter ?? null,
                "name_chief" => $data->name_chief ?? $data->name_pomoter,
                "phone_chief" => $data->phone_chief ?? $data->name_pomoter,
                "firstname_chief" => $data->firstname_chief,
                "email_chief" => $data->email_chief,
                "email" => $data->email,
                "phone" => $data->phone,
                "capacity" => $data->capacity,
                "coords" => $data->coords,
                "zone_file" => $filename,
                "town" => $data->town,
                "address" => $data->address,
                "social_reason" => $data->social_reason ?? null,
                "registered_number" => $data->registered_number ?? null,
                "registered_date" => isset($data->registered_date) ? date_create($data->registered_date) : null,
                "nature_promotor_id" => $data->nature_promotor_id,
                "registered_proof" => $filename2,
                "has_consent" => $data->has_consent ?? null,
                "head_office" => $data->head_office ?? null,
                "pomoter_is_director" => $data->chief_is_directeor,
                "consent_file" => $consent_file,
                "target" => json_encode($data->targets),
                "status" => $data->has_aggrement ? 8 : 0,
                "district_id" => (int)$data->district_id,
                "service_id" => (int)$request->service_id
            ], $dataR);

            $requete = new Requete($requeteData);
            $requete->save();

            if (isset($data->type_garderies)) {
                foreach ($data->type_garderies as $tg) {
                    $tgModel = new RequeteTypeGarderie([
                        "requete_id" => $requete->id,
                        "type_garderie_id" => $tg->id
                    ]);
                    $tgModel->save();
                }
            }

            // Gestion des fichiers init_code
            $files = RequeteFile::where('init_code', $request->init_code)->get();
            if (Storage::disk('doc_store')->exists($request->init_code)) {
                if (Storage::disk('doc_store')->exists($code)) {
                    File::copyDirectory(Storage::disk('doc_store')->path($request->init_code), Storage::disk('doc_store')->path($code));
                    File::deleteDirectory(Storage::disk('doc_store')->path($request->init_code));
                } else {
                    rename(Storage::disk('doc_store')->path($request->init_code), Storage::disk('doc_store')->path($code));
                }
            }
            foreach ($files as $value) {
                $value->update([
                    "init_code" => null,
                    "requete_id" => $requete->id,
                ]);
            }

            // Création récépissé PDF
            $recFile = time() . "recepice_inscription.pdf";
            $filePath = public_path('docs/' . $code . "/" . $recFile);
            $requeteFile = new RequeteFile([
                "type" => "PDF",
                "reference" => "Récépicé inscription",
                "filename" => $recFile,
                "level" => 1,
                "file_id" => null,
                "requete_id" => $requete->id,
            ]);
            $requeteFile->save();

            Pdf::loadView('emails.success_pj', [
                "cape" => $requete,
                "name" => $data->name,
                "cps" => $district->cps->name,
                "date" => date_format($requete->created_at, "d-m-Y"),
            ])->save($filePath);

            // Affectation et parcours
            $affectation = new Affectation([
                "user_up" => $user->id,
                "user_down" => $user->id,
                "requete_id" => $requete->id,
                "sens" => 1
            ]);
            $affectation->save();

            $parcours = new Parcours([
                'delay' => 0,
                'libelle' => "Dossier soumis par le centre " . $data->name,
                'requete_id' => $requete->id
            ]);
            $parcours->save();

            // Retourner le modèle Requete créé
            return $requete;
        }
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate($id, $data): Requete
    {
        $requete = Requete::whereCode($request->code)->first();
        $code = $request->code;
        $service = Service::find((int)$request->service_id);

        if ($service === null) {
            Log::error("Service non reconnu pour ID: " . $request->service_id);
            return null;
        }

        $filename = $requete->zone_file;
        if ($request->file('zone_file')) {
            $filename = FileStorage::setFile("doc_store", $request->file('zone_file'), $code, time());
        }

        $filename2 = $requete->registered_proof;
        if ($request->file('registered_proof')) {
            $filename2 = FileStorage::setFile("doc_store", $request->file('registered_proof'), $code, time());
        }

        $consent_file = $requete->consent_file;
        if ($request->file('consent_file')) {
            $consent_file = FileStorage::setFile("doc_store", $request->file('consent_file'), $code, time());
        }

        $data = json_decode($request->data);

        // Gestion des fichiers ZIP
        if ($request->file('files')) {
            $unzipper = new Unzip();
            $filenames = $unzipper->extract($request->file('files'), public_path('docs/' . $request->code));
            $fileInputs = $request->fileInputs ?? [];
            foreach ($filenames as $i => $value) {
                $requeteFile = new RequeteFile([
                    "type" => "PDF",
                    "level" => 0,
                    "reference" => $fileInputs[$i] ?? null,
                    "filename" => $value,
                    "requete_id" => $requete->id,
                ]);
                $requeteFile->save();
            }
        }

        // Mise à jour du modèle Requete
        $requete->fill([
            "name" => $data->name,
            "type_cape_id" => $data->type_cape_id ?? null,
            "name_pomoter" => $data->name_pomoter,
            "firstname_pomoter" => $data->firstname_pomoter ?? null,
            "phone_pomoter" => $data->phone_pomoter ?? null,
            "email_pomoter" => $data->email_pomoter ?? null,
            "name_chief" => $data->name_chief ?? $data->name_pomoter,
            "phone_chief" => $data->phone_chief ?? $data->name_pomoter,
            "firstname_chief" => $data->firstname_chief,
            "email_chief" => $data->email_chief,
            "email" => $data->email,
            "phone" => $data->phone,
            "capacity" => $data->capacity,
            "coords" => $data->coords,
            "zone_file" => $filename,
            "town" => $data->town,
            "address" => $data->address,
            "social_reason" => $data->social_reason ?? null,
            "registered_number" => $data->registered_number ?? null,
            "registered_date" => isset($data->registered_date) ? date_create($data->registered_date) : null,
            "nature_promotor_id" => $data->nature_promotor_id,
            "registered_proof" => $filename2,
            "has_consent" => $data->has_consent ?? null,
            "head_office" => $data->head_office ?? null,
            "pomoter_is_director" => $data->chief_is_directeor,
            "consent_file" => $consent_file,
            "target" => json_encode($data->targets),
            "district_id" => (int)$data->district_id,
            "service_id" => (int)$request->service_id,
            "status" => 3,
            "token" => null
        ]);
        $requete->save();

        // Création du parcours
        $parcours = new Parcours([
            'libelle' => "Dossier mise à jour par le centre " . $data->name,
            'requete_id' => $requete->id,
        ]);
        $parcours->save();

        // Envoi de mail (log seulement)
        Mailer::sendSimple(
            "emails.update_success",
            [],
            "Mise à jour dossier effectué",
            $data->name_pomoter,
            $data->email
        );

        // Retourner le modèle Requete mis à jour
        return $requete;
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
    public function setStatus(Request $request)
    {
        if ($request->decision == 1) {
            foreach (json_decode($request->requetes) as $value) {
                $requete = Requete::find($value->id);
                $requete->update([
                    'is_authorized' => $request->decision
                ]);

                Parcours::create([
                    'libelle' => "Enregistrement de la décision",
                    'requete_id' => $value->id,
                    'user_id' => Auth::id(),
                ]);
            }
        } else {
            foreach (json_decode($request->requetes) as $value) {
                $req = Requete::find($value->id);

                foreach (json_decode($request->decisions) as $key) {
                    Referal::create([
                        'libelle' => $key->observation,
                        'requete_id' => $req->id
                    ]);
                }

                $req->update([
                    'is_authorized' => $request->decision,
                    'final_observation' => $request->final_observation
                ]);

                Parcours::create([
                    'libelle' => "Enregistrement de la décision",
                    'requete_id' => $value->id,
                    'user_id' => Auth::id(),
                ]);
            }
        }
        // Retourner les requêtes mises à jour directement, sans JSON
        $updatedRequetes = Requete::whereIn('id', collect(json_decode($request->requetes))->pluck('id'))->get();

        return $updatedRequetes;
    }


    public function showResult($code)
    {
        $requete = Requete::where('code', $code)->first();
        return $requete;
    }


    public function show($code)
    {
        $requete = Requete::with([
        'files.file.TypeFile',
        'files2',
        'reponses.user',
        'parcours',
        'affectation',
        'TypeCape',
        'service',
        'RequeteTypeGarderies.TypeGarderie',
        'district.Municipality.Department',
        'NaturePromotor'
            ])->where('code', $code)->first();
            return $requete;
    }


    public function getForSession($code)
    {
        $requete = Requete::where('code', $code)->first();

        if ($requete) {
            if ($requete->session->is_active === null) {
                $requete->load([
                    'files.file',
                    'files2',
                    'session',
                    'myAvis' => function($q) {
                        $q->where('session_member_id', Auth::user()->session_member_id);
                    },
                    'TypeCape',
                    'service',
                    'district.Municipality.Department'
                ]);
            } elseif ($requete->session->is_active === true) {
                $requete->load([
                    'files.file',
                    'files2',
                    'avis.sm.member',
                    'session',
                    'myAvis' => function($q) {
                        $q->where('session_member_id', Auth::user()->session_member_id);
                    },
                    'TypeCape',
                    'service',
                    'district.Municipality.Department',
                    'referals'
                ]);
            } elseif ($requete->session->is_active === false) {
                $requete->load([
                    'files.file',
                    'files2',
                    'avis.sm.member',
                    'session',
                    'referals',
                    'TypeCape',
                    'service',
                    'district.Municipality.Department'
                ]);
            }
        }
        return $requete;
    }


    public function getNewRequete()
    {
        $requetes = collect(); // collection vide par défaut

        if (request()->service_id) {

            $role = Auth::user()->roles()->first()->name;
            $idUser = Auth::id();

            if ($role === "ddasm" || $role === "dfea") {
                $status = $role === "ddasm" ? 5 : 6;

                $requetes = Requete::with([
                    'files',
                    'reponses',
                    'parcours',
                    'affectation',
                    'TypeCape',
                    'service',
                    'RequeteTypeGarderies.TypeGarderie',
                    'NaturePromotor'
                ])
                ->where('status', $status)
                ->whereHas('affectations', function($q) use ($idUser) {
                    $q->where('user_down', $idUser)
                    ->where('isLast', true);
                })
                ->where('service_id', request()->service_id)
                ->orderBy('id', 'desc')
                ->get();

            } elseif ($role === "cps") {

                $requetes = Requete::with([
                    'files',
                    'reponses',
                    'parcours',
                    'affectation',
                    'TypeCape',
                    'service',
                    'RequeteTypeGarderies.TypeGarderie',
                    'NaturePromotor'
                ])
                ->whereIn('status', [0,1,2,3,4,5])
                ->whereHas('affectations', function($q) use ($idUser) {
                    $q->where('user_down', $idUser)
                    ->where('isLast', true);
                })
                ->where('service_id', request()->service_id)
                ->get();
            }
        }
        return $requetes; // retourne directement la collection ou un tableau vide
    }


    public function getTransmittedRequete()
    {
        $requetes = collect(); // valeur par défaut vide

        if (request()->service_id) {
            $role = Auth::user()->roles()->first()->name;
            $idUser = Auth::id();

            $relations = [
                'files',
                'reponses',
                'parcours',
                'affectation',
                'TypeCape',
                'service',
                'RequeteTypeGarderies.TypeGarderie',
                'NaturePromotor'
            ];

            if ($role === "ddasm" || $role === "dfea") {
                $status = $role === "ddasm" ? 5 : 6;

                $requetes = Requete::with($relations)
                    ->where('status', $status)
                    ->whereHas('affectations', function ($q) use ($idUser) {
                        $q->where('user_down', $idUser)
                        ->where('isLast', true);
                    })
                    ->where('service_id', request()->service_id)
                    ->get();

            } elseif ($role === "cps") {

                $districts = Auth::user()->cps?->districts->pluck('id') ?? [];

                $requetes = Requete::with($relations)
                    ->where('status', '>=', 5)
                    ->whereIn('district_id', $districts)
                    ->where('service_id', request()->service_id)
                    ->get();
            }
        }
        return $requetes; // retourne directement la collection ou vide
    }


    public function getPendingRequete()
    {
        $requetes = collect();
        if (request()->service_id) {
            $idUser = Auth::id();
            $requetes = Requete::with(['files','reponses','parcours','affectation'])
                ->where('status', 1)
                ->whereHas('affectations', function($q) use ($idUser) {
                    $q->where('user_down', $idUser)
                    ->where('isLast', true);
                })
                ->where('service_id', request()->service_id)
                ->get();
        }
        return $requetes;
    }


    public function getCorrectedRequete()
    {
        $requetes = collect();
        if (request()->service_id) {
            $idUser = Auth::id();
            $requetes = Requete::with(['files','reponses','parcours','affectation'])
                ->where('status', 3)
                ->whereHas('affectations', function($q) use ($idUser) {
                    $q->where('user_down', $idUser)
                    ->where('isLast', true);
                })
                ->where('service_id', request()->service_id)
                ->get();
        }
        return $requetes;
    }


    public function getRejectedRequete()
    {
        $requetes = collect();

        if (request()->service_id) {
            $idUser = Auth::id();
            $requetes = Requete::with(['files','reponses','parcours','affectation'])
                ->where('status', 2)
                ->whereHas('affectations', function($q) use ($idUser) {
                    $q->where('user_down', $idUser)
                    ->where('isLast', true);
                })
                ->where('service_id', request()->service_id)
                ->get();
        }
        return $requetes;
    }


    public function getValidatedRequete()
    {
        $requetes = collect();
            if (request()->service_id) {
                $idUser = Auth::id();
                $requetes = Requete::with(['files','reponses','parcours','affectation'])
                    ->where('status', 4)
                    ->whereHas('affectations', function($q) use ($idUser) {
                        $q->where('user_down', $idUser)
                        ->where('isLast', true);
                    })
                    ->where('service_id', request()->service_id)
                    ->get();
            }
        return $requetes;
    }


    public function getFinishedRequete()
    {
        $requetes = collect();
            if (request()->service_id) {
                $role = Auth::user()->roles()->first()->name;
                $idUser = Auth::id();

                if ($role == "dfea") {
                    $requetes = Requete::with(['files','reponses','parcours','affectation','TypeCape'])
                        ->where('status', 7)
                        ->where('session_id', null)
                        ->where('service_id', request()->service_id)
                        ->get();
                } elseif ($role == "cps") {
                    $requetes = Requete::with(['files','reponses','parcours','affectation'])
                        ->where('status', 5)
                        ->whereHas('affectations', function($q) use ($idUser) {
                            $q->where('user_down', $idUser)
                            ->where('isLast', true);
                        })
                        ->where('service_id', request()->service_id)
                        ->get();
                }
            }
            return $requetes;
    }


    public function getAdmissibleRequete()
    {
        $requetes = Requete::with(['files','reponses','parcours','affectation'])
            ->where('status', 7)
            ->where('session_id', null)
            ->get();
        return $requetes;
    }


    public function finishStore1(Request $request)
    {
        $code = $request->code;
        $datas = $request->all();
        $requete = Requete::where('code', $code)->first();

        if ($request->file('file')) {
            $filename = FileStorage::setFile("doc_store", $request->file('file'), $code, time());

            if ($requete->has_cps_file) {
                $reqFile = RequeteFile::where('reference', "Enquête sociale")
                    ->where('requete_id', $requete->id)
                    ->first();
                $reqFile->update([
                    "filename" => $filename,
                ]);
            } else {
                RequeteFile::create([
                    "type" => "PDF",
                    "reference" => "Enquête sociale",
                    "filename" => $filename,
                    "level" => 1,
                    "file_id" => null,
                    "requete_id" => $requete->id,
                ]);
            }

            unset($datas['file']);
        }

        // Crée un nouveau parcours
        Parcours::create([
            'libelle' => "Enregistrement de l'enquête sociale",
            'requete_id' => $requete->id,
            'user_id' => Auth::id()
        ]);

        // Met à jour le modèle
        $requete->update(array_merge($datas, [
            'has_cps_file' => true,
            'status' => $requete->has_physical_deposit ? 5 : $requete->status
        ]));

        return $requete;
    }

    public function finishStore2(Request $request)
    {
        $requete = null;

        if ($request->has('codeForRecepisse')) {
            $requete = Requete::where('code', $request->input('codeForRecepisse'))->first();

            if ($requete && $requete->receipt_preview) {
                $directoryPath = public_path('docs/' . $requete->code);
                $filePattern = $directoryPath . '/' . $requete->receipt_preview;
                $files = glob($filePattern);

                if (!empty($files)) {
                    $filePath = $files[0];

                    $emails = collect([
                        ['email' => $requete->email, 'name' => $requete->name],
                        ['email' => $requete->email_pomoter, 'name' => 'Monsieur/Madame Promotteur du centre ' . $requete->name_pomoter],
                        ['email' => $requete->email_chief, 'name' => 'Monsieur/Madame Directeur du centre ' . $requete->name_chief],
                    ])->unique('email');

                    foreach ($emails as $item) {
                        Mailer::sendSimpleWithFile(
                            "emails.deposit_recepisse",
                            ['name' => $item['name']],
                            "Récépissé de dépôt physique",
                            $item['name'],
                            $item['email'],
                            [$filePath]
                        );
                    }
                }
            }
        } else {
            $requete = Requete::where('code', $request->code)->first();
            $datas = $request->all();
            $code = $request->code;
            $datas['date_depositor'] = date_create($datas['date_depositor']);
            unset($datas['code']);

            $recFile = time() . "recepisse_depot_physique_preview.pdf";
            $filePath = public_path('docs/' . $code . "/" . $recFile);

            // Préparer PDF et QR code
            $qrcode = QrCode::size(200)->generate(env('APP_FRONT_URL') . '/verify-document/' . Str::uuid());
            $decret = strtolower($requete->service?->name) == "cape"
                ? "Dans le but de se conformer aux dispositions du décret N° 2022-072  du 09 février 2022 fixant les modalités de création, d'organisation et de fonctionnement des centres d'accueil et de protection de l'enfant en République du Bénin"
                : "Dans le but de se conformer aux dispositions du décret N° 2023-291  du 31 MAI 2023 fixant les modalités de création, d'organisation et de fonctionnement des garderies d'enfants en République du Bénin";

            Pdf::loadView('emails.deposit_receipt_pj', [
                "decret" => $decret,
                "cape" => $requete,
                "token" => $datas['token'] ?? Str::uuid(),
                "qrcode" => $qrcode,
            ])->save($filePath);

            RequeteFile::create([
                "type" => "PDF",
                "reference" => "Récépissé du dépôt de physique",
                "filename" => $recFile,
                "level" => 1,
                "file_id" => null,
                "token" => $datas['token'] ?? Str::uuid(),
                "delivery_by" => Auth::id(),
                "requete_id" => $requete->id,
            ]);

            Parcours::create([
                'libelle' => "Enregistrement des données du dépôt physique",
                'requete_id' => $requete->id,
                'user_id' => Auth::id()
            ]);

            $emails = collect([
                ['email' => $requete->email, 'name' => $requete->name],
                ['email' => $requete->email_pomoter, 'name' => 'Monsieur/Madame Promotteur du centre ' . $requete->name_pomoter],
                ['email' => $requete->email_chief, 'name' => 'Monsieur/Madame Directeur du centre ' . $requete->name_chief],
            ])->unique('email');

            foreach ($emails as $item) {
                Mailer::sendSimpleWithFile(
                    "emails.deposit_recepisse",
                    ['name' => $item['name']],
                    "Récépissé de dépôt physique",
                    $item['name'],
                    $item['email'],
                    [$filePath]
                );
            }

            $requete->update(array_merge($datas, [
                'has_physical_deposit' => true,
                'receipt_preview' => $recFile
            ]));
        }
        // Retourne directement le modèle Requete
        return $requete;
    }


    public function transUp(Request $request)
    {
        $requete = Requete::where('code', $request->code)->first();
        $message = "";
        $status = $requete->status;
        $user = null;

        // Gestion des statuts et affectations
        if ($requete->status == 5 || $requete->affectation->sens == -1) {
            if (Auth::user()->roles()->first()->name == "cps") {
                $role = Role::where('name', 'ddasm')->first();
                $user = User::role($role)->where("department_id", $requete?->district?->municipality?->department?->id)->first();
                if (!$user) {
                    $message = "Veuillez contacter l'administrateur, compte DDASM non actif";
                }
                $libelle = "Dossier validé transmis au DDASM";
                $status = 5;
            } else {
                $role = Role::where('name', 'dfea')->first();
                $user = User::role($role)->first();
                $message = "Compte DDASM non actif veuillez contacter l'administrateur";
                $libelle = "Dossier validé transmis au DDASM";
                $status = 6;
            }
        } elseif ($requete->status == 6) {
            $role = Role::where('name', 'dfea')->first();
            $user = User::role($role)->first();
            $message = "Compte DFEA non actif veuillez contacter l'administrateur";
            $libelle = "Dossier recevable pour inscription";
            $status = 7;
        }

        // Création de la réponse et affectation si un user est disponible
        if ($user) {
            Response::create([
                "user_id" => Auth::id(),
                "observation" => $request->observation,
                "requete_id" => $requete->id
            ]);

            $requete->affectation->update(['isLast' => false]);

            Affectation::create([
                "user_up" => Auth::id(),
                "user_down" => $user->id,
                "isLast" => true,
                "requete_id" => $requete->id
            ]);

            $requete->update(['status' => $status]);

            Parcours::create([
                'libelle' => $libelle,
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        } else {
            Parcours::create([
                'libelle' => $libelle,
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        }

        // Retourne directement le modèle Requete mis à jour
        return $requete;
    }


    public function transDown(Request $request)
    {
        $requete = Requete::where('code', $request->code)->first();
        $status = $requete->status;

        // Ajustement du statut
        if ($requete->status == 5) {
            $status = 4;
        } elseif ($requete->status == 6) {
            $status = 5;
        }

        // Récupération de l'utilisateur précédent et mise à jour de l'affectation
        $user_down = $requete->affectation->user_up;
        $requete->affectation->update(['isLast' => false]);

        Affectation::create([
            "user_up"    => Auth::id(),
            "user_down"  => $user_down,
            "isLast"     => true,
            "sens"       => -1,
            "instruction"=> $request->motif,
            "requete_id" => $requete->id
        ]);

        // Mise à jour du statut du dossier
        $requete->update(['status' => $status]);

        // Création du parcours
        Parcours::create([
            'libelle'    => "Retour du dossier pour correction",
            'requete_id' => $requete->id,
            'user_id'    => Auth::id(),
        ]);

        // Retourne le modèle Requete mis à jour
        return $requete;
    }


    public function inviteStore(Request $request)
    {
        try {
        $requete = Requete::find($request->id);

        // Vérification des fichiers non validés
        $checkFileTreatedValid = RequeteFile::where('requete_id', $requete->id)
            ->where(function($q) {
                $q->where('is_treated', true)
                ->where('is_valid', false)
                ->whereNotNull('file_id');
            })
            ->orWhere(function($q) {
                $q->where('is_treated', false)
                ->whereNotNull('file_id');
            })
            ->get();

        if ($checkFileTreatedValid->count() > 0) {
            throw new \Exception("Tous les documents n'ont pas été validés");
        }

        // Envoi du mail
        Mailer::sendSimple(
            "emails.success",
            [
                "cps" => $requete?->district?->cps?->name,
                "date_meeting" => $request->date_meeting,
            ],
            "Invitation au dépôt du dossier physique",
            $requete->name_pomoter,
            $requete->email
        );

        // Création du parcours
        Parcours::create([
            'delay' => $this->getDelay($requete->id),
            'libelle' => "Envoi de mail d'invitation au CAPE",
            'requete_id' => $requete->id,
            'user_id' => Auth::id(),
        ]);

        // Mise à jour du statut
        $requete->update(['status' => 4]);

        // Création de l'agenda
        Agenda::create([
            'invite_date' => date_create($request->date_meeting),
            'cps_id' => $requete?->district?->cps?->id,
            'status' => $requete->id,
        ]);

        // Retourne le modèle mis à jour
        return $requete;

        } catch (\Exception $e) {
            Log::error($e);
            throw $e; // relancer l'exception pour gestion ailleurs
        }
    }


    public function setDecision(Request $request)
    {
        $requete = Requete::find($request->id);
        $avis = [];

        if ($request->decision == 0) {
            // Récupération des avis de rejet
            foreach ($requete->files as $file) {
                foreach ($file->avis as $a) {
                    $avis[] = $a;
                }
            }

            Parcours::create([
                'libelle' => "Envoi des mails de rejet au CAPE",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);

            Mailer::sendSimple(
                "emails.rejected",
                [
                    "observation" => $request->observation,
                    "motifs" => $avis
                ],
                "Décision du conseil",
                $requete->name_promoter,
                $requete->email
            );

        } else {
            // Enregistrement des recommandations
            foreach (json_decode($request->referals) as $value) {
                Referal::create([
                    "libelle" => $value->observation,
                    "requete_id" => $requete->id
                ]);
            }

            Parcours::create([
                'libelle' => "Enregistrement des recommandations issues de la session",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        }

        // Mise à jour du modèle
        $requete->update([
            "has_agreemant" => $request->decision,
            "observation" => $request->observation,
            "note" => $request->note
        ]);

        // Retourne le modèle mis à jour
        return $requete;
    }


    public function authorized()
    {
        $session = Session::where('is_active', false)->orderBy("id", "desc")->first();
        $requetes = Requete::where('session_id', $session->id)->where('is_authorized', true)->get();

        foreach ($requetes as $value) {
            $check = Cape::where('requete_id', $value->id)->first();
            if ($check === null) {
                // Création du Cape
                $cape = Cape::create([
                    "requete_id" => $value->id,
                    "status" => 1
                ]);

                // Mise à jour des référals
                $refs = Referal::where('requete_id', $value->id)->get();
                foreach ($refs as $ref) {
                    $ref->update(['cape_id' => $cape->id]);
                }

                // Création de l'utilisateur CAPE
                $password = Str::random(8);
                $datas = [
                    "code" => Str::uuid(),
                    "name" => $value->name_pomoter . " " . $value->firstname_pomoter,
                    "email" => $value->email,
                    "password" => Hash::make($password),
                    "cape_id" => $cape->id
                ];
                $user = User::create($datas);
                $user->assignRole(Role::whereName('cape')->first());

                // Génération du PDF d'agrément
                $recFile = time() . "agrement.pdf";
                $filePath = public_path('docs/' . $value->code . "/" . $recFile);
                RequeteFile::create([
                    "type" => "PDF",
                    "reference" => "Agrément d'autorisation",
                    "filename" => $recFile,
                    "level" => 1,
                    "file_id" => null,
                    "requete_id" => $value->id,
                ]);

                Pdf::loadView('emails.agrement_pj', [
                    "name" => $value->name
                ])->save($filePath);

                // Envoi du mail avec fichier
                Mailer::sendSimpleWithFile(
                    "emails.cape_account",
                    [
                        "name" => $value->name,
                        "cape" => $value,
                        "password" => $password
                    ],
                    "Compte d'accès CAPE",
                    $value->name_promoter,
                    $value->email,
                    [$filePath]
                );

                // Création du parcours
                Parcours::create([
                    'libelle' => "Délivrance d'autorisation au CAPE",
                    'requete_id' => $value->id,
                    'user_id' => Auth::id(),
                ]);

                // Mise à jour de la requête
                $value->update([
                    "can_closed" => true,
                    "is_validated" => true
                ]);
            }
        }

        // Retourne les requêtes mises à jour
        return $requetes;
    }


    public function getDecision()
    {
        $session = Session::where('is_active', false)
            ->orderBy('id', 'desc')
            ->first();

        if (!$session) {
            return null; // ou éventuellement collect() vide
        }

        $requetes = Requete::where('session_id', $session->id)
            ->where('can_closed', false)
            ->where('status', '<>', 8)
            ->get();

        return $requetes;
    }


    public function getListForPublic($serviceId)
    {
        $requetes = Requete::with(['TypeCape','service','Cape','district.municipality.department','district.cps'])
            ->where(function($query) use ($serviceId) {
                $query->where('is_authorized', true)
                    ->where('service_id', $serviceId)
                    ->where('is_validated', true)
                    ->whereNotNull('session_id');
            })
            ->orWhere(function($query) {
                $query->where('status', 8)
                    ->where('is_validated', true);
            })
            ->get();

        // Retour direct des modèles, pas de JSON
        return $requetes;
    }
    

    public function getPendingValidation()
    {
        $requetes = Requete::with(['TypeCape','service','Cape','district.municipality.department','district.cps'])
            ->where('status', 8)
            ->get();

        return $requetes;
    }


    public function setStatus2(Request $request)
    {
        $req = Requete::find($request->id);
        $req->update([
            'is_validated' => $request->decision,
        ]);
        return $req;
    }


    public function generateUniqueCode($service)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $prefix = $service->name . '-';
        $codeLength = 6;

        do {
            $code = '';
            for ($i = 0; $i < $codeLength; $i++) {
                $position = rand(0, $charactersNumber - 1);
                $code .= $characters[$position];
            }
        } while (Requete::where('code', $prefix . $code)->exists());

        return $prefix . $code;
    }


    public function setFileTreatment(Request $request)
    {
        $reqFile = RequeteFile::find($request->id);

        if ($reqFile) {
            $reqFile->update([
                "is_treated" => true,
                "observation" => $request->observation,
                "is_valid" => $request->is_valid
            ]);

            return "Traitement de fichier effectué";
        } else {
            return "Fichier introuvable";
        }
    }


    public function getDelay($d)
    {
        $lp = Requete::find($d)->lastParcours;
        if ($lp) {
            $now = time(); // temps actuel en secondes
            $lastDate = strtotime($lp->created_at); // date du dernier parcours en secondes
            $datediff = $now - $lastDate; // différence en secondes
            $days = round($datediff / (60 * 60 * 24)); // conversion en jours
            return $days;
        } else {
            return 0; // ou null si aucun parcours trouvé
        }
    }







}
