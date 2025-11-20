<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Utilities\Mailer;
use App\Utilities\FileStorage;
use App\Models\District;
use App\Models\Agenda;
use App\Models\Requete;
use App\Models\RequeteFile;
use App\Models\User;
use App\Models\Session;
use App\Models\Affectation;
use App\Models\Parcours;
use App\Models\Response;
use App\Models\Referal;
use App\Models\Service;
use VIPSoft\Unzip\Unzip;
use Str,File,Auth,Hash,QrCode,Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cape;
use App\Models\RequeteTypeGarderie;
use Log;

/** status check
 * 0 : Nouvelle
 * 1 : Mise en attente
 * 2 : Rejeté
 * 3 : Corrigé
 * 4 : Validé
 * 5 : Finalisé
 * 6 : Visa-DDASM
 * 7 : A inscrire
 * 8 : Inscription avec agrément
 * 9 : Ancien Cape Inscrit
 */
class RequeteController extends Controller
{

    public function __construct() {
      
        $this->middleware('auth', ['except' => ['getListForPublic','showResult']]);
    }


    public function index($service_id=null)
    {

        $requetes=[];

        if (request()->service_id) {
            $role=Auth::user()->roles()->first()->name;

            switch ($role) {
                case 'cps':
                    $districtIds=Auth::user()->cps->districts->pluck('id');
                    $requetes=Requete::with(['parcours.user','TypeCape','service','lastParcours'])->whereIn('district_id',$districtIds)->where('service_id',request()->service_id)->orderBy("id","desc")->get();
                    break;
                case 'ddasm':
                    $districtIds=[];
                    $k=0;
                    foreach (Auth::user()->department->municipalities as  $value) {
                        foreach ($value->districts as $value2) {
                            $districtIds[$k]= $value2->id;
                            $k++;
                        }
                     
                    }
                    
                    $requetes=Requete::with(['parcours.user','TypeCape','service','district.cps','lastParcours'])->whereIn('district_id',$districtIds)->where('service_id',request()->service_id)->orderBy("id","desc")->get();
                                    break;
                case 'dfea':
                    $requetes=Requete::with(['parcours.user','TypeCape','service','lastParcours'])->where('service_id',request()->service_id)->orderBy("id","desc")->get();
                    break;
                case 'ministre':
                    $requetes=Requete::with(['parcours.user','TypeCape','service','lastParcours'])->where('service_id',request()->service_id)->orderBy("id","desc")->get();
                    break;

                case 'Promoteur':
                    
                    $requetes=Requete::with(['parcours.user','TypeCape','service','lastParcours','files.file.TypeFile','files2'])->where('promoter_id',Auth::user()->promoter_id)->orderBy("id","desc")->get();
                    break;
                
                default:
                   $requetes=[];
                    break;
            }
        } else{
                                $requetes=Requete::with(['parcours.user','TypeCape','service','lastParcours','files.file.TypeFile','files2'])->where('promoter_id',Auth::user()->promoter_id)->orderBy("id","desc")->get();

        }
        
      
           return response()->json([
            "success"=>true,
            "message"=>"Liste des recommendations",
            "data"=>$requetes
        ],200);  

    }
    
    static public function store($request)
{
    if ($request->input('codeForRecepisse')) {
      //  try {
            $requete = Requete::where('code', $request->input('codeForRecepisse'))->first();
            $requeteFile = RequeteFile::where('requete_id', $requete->id)->where('reference', 'Récépissé inscription')->first();

            $service = Service::find((int) $requete->service_id);


            $directoryPath = public_path('docs/' . $requete->code);
            $filePattern = $directoryPath . '/' . $requeteFile->filename;


            $files = glob($filePattern);

            if (!empty($files)) {
                $filePath = $files[0];
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Le fichier du récépissé d'inscription n'a pas été trouvé",
                    "data" => null
                ], 200);
            }

            $emails = [
                [
                    'email' => $requete->email,
                    'name' => "A l'attention de " . $requete->name
                ],
                [
                    'email' => $requete->email_pomoter,
                    'name' => 'Cher Monsieur/Madame ' . $requete->name_pomoter . ' ' . $requete->firstname_pomoter . ', promoteur du centre ' . $requete->name
                ],
                [
                    'email' => $requete->email_chief,
                    'name' => 'Cher Monsieur/Madame ' . $requete->name_chief . ' ' . $requete->firstname_chief . ', promoteur du centre ' . $requete->name
                ],
            ];

            $uniqueEmails = [];
            $seenEmails = [];

            foreach ($emails as $email) {
                if (!in_array($email['email'], $seenEmails)) {
                    $uniqueEmails[] = $email;
                    $seenEmails[] = $email['email'];
                }
            }

            $emails = $uniqueEmails;

            $allMailsSent = true;

            foreach ($emails as $item) {
                $mailResult = Mailer::sendSimpleWithFile(
                    "emails.submit_success",
                    [
                            "cape" => $requete,
                            "name" => $item['name'],
                            'code' => $request->input('codeForRecepisse'),
                            'service' => $service,
                            'cps' => $requete->district->cps->name

                        ],
                    "Soumission de dossier CAPE/Garderie",
                    $item['name'],
                     $item['email'],
                    //'alexiskatel92@gmail.com',
                    [$filePath],
                );
                if (!$mailResult['success']) {
                    $allMailsSent = false;
                    Log::error('Échec d\'envoi à ' . $item['email']);
                }
            }

            // Condition pour le retour de réponse
            if ($allMailsSent) {
                return response()->json([
                    "success" => true,
                    "message" => 'Récépissé envoyé !',
                    "data" => null
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => 'Échec de l\'envoi du récépissé',
                    "data" => null
                ], 500);
            }
        // } catch (\Exception $th) {
        //     Log::error($th);
        // }


    } else {
        $data = json_decode($request->data);
        $district = District::find($data->district_id);
        if ($district->cps == null) {
            return response()->json([
                "success" => true,
                "message" => "Veuillez contacter l'administrateur!Arrondissement non classé",
                "data" => null
            ], 500);
        }
        $user = User::where('cps_id', $district->cps->id)->first();

        if ($user == null) {
            return response()->json([
                "success" => true,
                "message" => "Veuillez contacter l'administrateur! Compte Cps inexistant",
                "data" => null
            ], 500);
        }
        // $code=Str::uuid();
        $service = Service::find((int) $request->service_id);

        if ($service == null) {
            return response()->json([
                "success" => true,
                "message" => "Veuillez contacter l'administrateur! Service non reconnu",
                "data" => null
            ], 500);
        }

        $check = Requete::where("name_pomoter", $data->name_pomoter)
            ->where("firstname_pomoter", $data->firstname_pomoter)
            ->where("name", $data->name)->first();
        if ($check) {
            return response()->json([
                "success" => true,
                "message" => "Il existe déjà un CAPE enregistré sous ce nom pour ce promoteur. Veuillez contacter le support pour plus de détails",
                "data" => null
            ], 500);
        }

        $code = RequeteController::generateUniqueCode($service);
        $dataR = [];
        if ($data->has_aggrement) {
            $dataR['aggreement_reference'] = $data->aggreement_reference;
            $dataR['aggreement_year'] = $data->aggreement_year;
            $dataR['file_aggreement'] = FileStorage::setFile("doc_store", $request->file('file_aggreement'), $code, time());
            $dataR['has_agreemant'] = true;
            $dataR['is_authorized'] = true;
        }
        $filename = null;
        if ($request->file('zone_file')) {
            $filename = FileStorage::setFile("doc_store", $request->file('zone_file'), $code, time());
        }

        $filename2 = null;
        if ($request->file('registered_proof')) {
            $filename2 = FileStorage::setFile("doc_store", $request->file('registered_proof'), $code, time());
        }

        $consent_file = null;
        if ($request->file('consent_file')) {
            $consent_file = FileStorage::setFile("doc_store", $request->file('consent_file'), $code, time());

        }
        $requete = Requete::create(array_merge([
            "code" => $code,
            "name" => $data->name,
            "type_cape_id" => isset($data->type_cape_id) ? (int) $data->type_cape_id : null,
            // "type_garderie_id"=>isset($data->type_garderie_id)?(int)$data->type_garderie_id:null,
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
            "social_reason" => isset($data->social_reason) ? $data->social_reason : null,
            "registered_number" => isset($data->registered_number) ? $data->registered_number : null,
            "registered_date" => isset($data->registered_date) ? date_create($data->registered_date) : null,
            "nature_promotor_id" => $data->nature_promotor_id,
            "registered_proof" => $filename2 ?? null,
            "has_consent" => isset($data->has_consent) ? $data->has_consent : null,
            "head_office" => isset($data->head_office) ? $data->head_office : null,
            "pomoter_is_director" => $data->chief_is_directeor,
            "consent_file" => $consent_file,
            "target" => json_encode($data->targets),
            "status" => $data->has_aggrement ? 8 : 0,
            "district_id" => (int) $data->district_id,
            "service_id" => (int) $request->service_id,
            "promoter_id"=>Auth::user()->promoter_id
        ], $dataR));

        if (isset($data?->type_garderies)) {
            foreach ($data->type_garderies as $tg) {
                RequeteTypeGarderie::create([
                    "requete_id" => $requete->id,
                    "type_garderie_id" => $tg->id
                ]);
            }
        }
        $files = RequeteFile::where('init_code', $request->init_code)->get();
        if (Storage::disk('doc_store')->exists($request->init_code)) {
            if (Storage::disk('doc_store')->exists($code)) {
                File::copyDirectory(Storage::disk('doc_store')->path($request->init_code), Storage::disk('doc_store')->path($code));
                File::deleteDirectory(Storage::disk('doc_store')->path($request->init_code));
            } else {
                rename(Storage::disk('doc_store')->path($request->init_code), Storage::disk('doc_store')->path($code));
            }
        }
        foreach ($files as $key => $value) {
            // FileStorage::moveFile('doc_store',$code,'tmp/'.$value->filename,$code.'/'.$value->filename);
            $value->update([
                "init_code" => null,
                "requete_id" => $requete->id,
            ]);
        }
        // foreach (json_decode($request->input('files')) as $value) {
        //     $filename= FileStorage::set64File("doc_store",$value->file64,$code,$value->name.Str::random());
        //     RequeteFile::create([
        //         "type"=>"PDF",
        //         "reference"=>$value->name,
        //         "filename"=>$filename,
        //         "level"=>0,
        //         "file_id"=>$value->file_id,
        //         "requete_id"=>$requete->id,
        //     ]);
        // }
        $recFile = time() . "recepice_inscription.pdf";
        $filePath = public_path('docs/' . $code . "/" . $recFile);
        RequeteFile::create([
            "type" => "PDF",
            "reference" => "Récépissé inscription",
            "filename" => $recFile,
            "level" => 1,
            "file_id" => null,
            "promoter_id"=>Auth::user()->promoter_id,
            "requete_id" => $requete->id,
        ]);





        $cape = $requete;
        $cape->district->municipality->departement;
        $cape->district->cps;
        Pdf::loadView('emails.success_pj', [
            "cape" => $cape,
            "name" => $data->name,
            "cps" => $district->cps->name,
            "date" => date_format($requete->created_at, "d-m-Y"),
        ])->save($filePath);

        Affectation::create([
            "user_up" => $user->id,
            "user_down" => $user->id,
            "requete_id" => $requete->id,
            "sens" => 1
        ]);

        Parcours::create([
            'delay' => 0,
            'libelle' => "Dossier soumis par le centre " . $data->name,
            'requete_id' => $requete->id
        ]);


        $emails = [
            [
                'email' => $cape->email,
                'name' => "A l'attention de " . $cape->name
            ],
            [
                'email' => $cape->email_pomoter,
                'name' => 'Cher Monsieur/Madame ' . $cape->name_pomoter . ' ' . $cape->firstname_pomoter . ', promoteur du centre ' . $cape->name
            ],
            [
                'email' => $cape->email_chief,
                'name' => 'Cher Monsieur/Madame ' . $cape->name_chief . ' ' . $cape->firstname_chief . ', directeur du centre ' . $cape->name
            ],
        ];

        $uniqueEmails = [];
        $seenEmails = [];

        foreach ($emails as $email) {
            if (!in_array($email['email'], $seenEmails)) {
                $uniqueEmails[] = $email;
                $seenEmails[] = $email['email'];
            }
        }

        $emails = $uniqueEmails;

        $allMailsSent = true;

        foreach ($emails as $item) {
            $mailResult = Mailer::sendSimpleWithFile(
                "emails.submit_success",
                [
                        "cape" => $cape,
                        "name" => $item['name'],
                        'code' => $request->input('codeForRecepisse'),
                        'service' => $service,
                        'cps' => $cape->district->cps->name

                    ],
                "Soumission de dossier CAPE/Garderie",
                $item['name'],
                $item['email'],
                //'alexiskatel92@gmail.com',
                [$filePath],
            );
            if (!$mailResult) {
                $allMailsSent = false;
                Log::error('Échec d\'envoi à ' . $item['email']);
            }
        }

        // Condition pour le retour de réponse
        if ($allMailsSent) {
            Log::info('Récépissé envoyé !');

        } else {
            Log::error('Échec de l\'envoi du récépissé');
        }


        // Mailer::sendSimpleWithFile(
        //     "emails.submit_success",
        //     [
        //         "cape" => $cape,
        //         'code' => $code,
        //         'service' => $service,
        //         'cps' => $cape->district->cps->name

        //     ],
        //     "Soumission de dossier CAPE/Garderie",
        //     $data->name_pomoter,
        //     $data->email,
        //     [$filePath]
        // );


        return response()->json([
            "success" => true,
            "message" => "Soumission de dossier pour demande d'autorisation effectué",
            "data" => null
        ], 200);
    }
}

    static public function update($request)
    {
        $requete=Requete::whereCode($request->code)->first();
        $code = $request->code;
        $service =Service::find((int)$request->service_id);

        if($service==null){
         return response()->json([
             "success"=>true,
             "message"=>"Veuillez contacter l'administrateur! Service non reconnu",
             "data"=>null
         ],500);
        }

        $filename=$requete->zone_file;
        if ($request->file('zone_file')) {
            $filename= FileStorage::setFile("doc_store",$request->file('zone_file'),$code,time());
        }

        $filename2=$requete->registered_proof;
        if ($request->file('registered_proof')) {
            $filename2= FileStorage::setFile("doc_store",$request->file('registered_proof'),$code,time());
        }


        $consent_file=$requete->consent_file;
        if ($request->file('consent_file')) {
            $consent_file= FileStorage::setFile("doc_store",$request->file('consent_file'),$code,time());

        }
        $data=json_decode($request->data);


      
        if ($request->file('files')) {
            $unzipper  = new Unzip();
            $filenames = $unzipper->extract($request->file('files'),public_path('docs/'.$request->code));
            $fileInputs=$request->fileInputs;
            $i=0;
            foreach ($filenames as $value) {
                //$filename= FileStorage::setFile("doc_store",$value,$code,time());
                RequeteFile::create([
                    "type"=>"PDF",
                    "level"=>0,
                    "reference"=>$fileInputs[$i],
                    "filename"=>$value,
                    "requete_id"=>$requete->id,
                ]);
                $i++;
            }
        }
      
        $requete->update([
            "name"=>$data->name,
            "type_cape_id"=>isset($data->type_cape_id)?(int)$data->type_cape_id:null,
           // "type_garderie_id"=>isset($data->type_garderie_id)?(int)$data->type_garderie_id:null,
            "name_pomoter"=>$data->name_pomoter,
            "firstname_pomoter"=>$data->firstname_pomoter??null,
            "phone_pomoter"=>$data->phone_pomoter??null,
            "email_pomoter"=>$data->email_pomoter??null,
            "name_chief"=>$data->name_chief??$data->name_pomoter,
            "phone_chief"=>$data->phone_chief??$data->name_pomoter,
            "firstname_chief"=>$data->firstname_chief,
            "email_chief"=>$data->email_chief,
            "email"=>$data->email,
            "phone"=>$data->phone,
            "capacity"=>$data->capacity,
            "coords"=>$data->coords,
            "zone_file"=>$filename,
            "town"=>$data->town,
            "address"=>$data->address,
            "social_reason"=>isset($data->social_reason)?$data->social_reason:null,
            "registered_number"=>isset($data->registered_number)?$data->registered_number :null,
            "registered_date"=>isset($data->registered_date)?date_create($data->registered_date):null,
            "nature_promotor_id"=>$data->nature_promotor_id,
            "registered_proof"=>$filename2??null,
            "has_consent"=>isset($data->has_consent)?$data->has_consent:null,
            "head_office"=>isset($data->head_office) ?$data->head_office:null,
            "pomoter_is_director"=>$data->chief_is_directeor,
            "consent_file"=>$consent_file,
            "target"=>json_encode($data->targets),
            "district_id"=> (int)$data->district_id,
            "service_id"=> (int)$request->service_id,
           "status"=>3,
           "token"=>null
       ]);

      

        Parcours::create([
            'libelle'=>"Dossier mise à jour par le centre ".$data->name,
            'requete_id'=>$requete->id,
        ]);


        Mailer::sendSimple(
            "emails.update_success",
            [],
            "Mise à jour dossier effectué",
            $data->name_pomoter,
            $data->email);

         
        return response()->json([
            "success"=>true,
            "message"=>"Mise à jour de dossier pour demande d'autorisation effectué",
            "data"=>null
        ],200);
    }


    public function showResult($code)
    {
        $requetes=Requete::where('code',$code)->first();

     

        if ($requetes) {

            return response()->json($requetes, 200);

        }else {
       
            return response()->json([], 500);

        }
    }
    public function show($code)
    {
        $requetes=Requete::with(['files.file.TypeFile','files2','reponses.user','parcours','affectation','TypeCape','service','RequeteTypeGarderies.TypeGarderie','district.Municipality.Department','RequeteTypeGarderies.TypeGarderie','service','NaturePromotor'])->where('code',$code)->first();

        return response()->json($requetes, 200);
    }

    public function getForSession($code)
    {
        $requete=Requete::where('code',$code)->first();
       
        if ($requete->session->is_active ===null) {
            $requetes=Requete::with(['files.file','files2','session', 'myAvis'=>function($q){$q->where('session_member_id',Auth::user()->session_member_id);} ,'TypeCape','service','district.Municipality.Department'])->where('code',$code)->first();
        }else
        if ($requete->session->is_active ==true) {
         
            $requetes=Requete::with(['files.file','files2','avis.sm.member','session','myAvis'=>function($q){$q->where('session_member_id',Auth::user()->session_member_id);},'TypeCape','service','district.Municipality.Department','referals'])->where('code',$code)->first();
        }elseif ($requete->session->is_active==false) {

            $requetes=Requete::with(['files.file','files2','avis.sm.member','session','referals','TypeCape','service','district.Municipality.Department'])->where('code',$code)->first();

        } 

        return response()->json($requetes, 200);
    }

    public function getNewRequete()
    {

        $requetes=[];

        if (request()->service_id) {

        $role=Auth::user()->roles()->first()->name;
        if ($role=="ddasm" || $role=="dfea") {
            $status= $role=="ddasm" ?5:6;
            $idUser=Auth::id();

            $requetes=Requete::with(['files','reponses','parcours','affectation','TypeCape','service','RequeteTypeGarderies.TypeGarderie','service','NaturePromotor'])->where('status', $status)->whereHas('affectations', function($q) use($idUser) {
                $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
                })->where('service_id',request()->service_id)->orderBy("id",'desc')->get();
                return response()->json($requetes, 200);
        }else if($role="cps") {
            $idUser=Auth::id();
            $requetes=Requete::with(['files','reponses','parcours','affectation','TypeCape','service','RequeteTypeGarderies.TypeGarderie','service','NaturePromotor'])->whereIn('status',[0,1,2,3,4,5])->whereHas('affectations', function($q) use($idUser) {
                $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
                })->where('service_id',request()->service_id)->get();
                return response()->json($requetes, 200);
        }
    }
        return response()->json([], 200);
    }
    public function getTransmittedRequete()
    {
        $requetes=[];

        if (request()->service_id) {
        $role=Auth::user()->roles()->first()->name;
        if ($role=="ddasm" || $role=="dfea") {
            $status= $role=="ddasm" ?5:6;
            $idUser=Auth::id();

            $requetes=Requete::with(['files','reponses','parcours','affectation','TypeCape','service','RequeteTypeGarderies.TypeGarderie','service','NaturePromotor'])->where('status', $status)->whereHas('affectations', function($q) use($idUser) {
                $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
                })->where('service_id',request()->service_id)->get();
                return response()->json($requetes, 200);
        }else if($role="cps") {
            $idUser=Auth::id();
            $requetes=Requete::with(['files','reponses','parcours','affectation','TypeCape','service','RequeteTypeGarderies.TypeGarderie','service','NaturePromotor'])->where('status',">=",5)->whereIn('district_id',Auth::user()->cps?->districts->pluck('id'))->where('service_id',request()->service_id)->get();
                return response()->json($requetes, 200);
        }}
        return response()->json([], 200);
    }

    public function getPendingRequete()
    {

        $requetes=[];

        if (request()->service_id) {
        $idUser=Auth::id();
        $requetes=Requete::with(['files','reponses','parcours','affectation'])->where('status',1)->whereHas('affectations', function($q) use($idUser) {
            $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
            })->where('service_id',request()->service_id)->get();
        }
            return response()->json($requetes, 200);
    }
    public function getCorrectedRequete()
    {
        $requetes=[];

        if (request()->service_id) {
        $idUser=Auth::id();
        $requetes=Requete::with(['files','reponses','parcours','affectation'])->where('status',3)->whereHas('affectations', function($q) use($idUser) {
            $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
            })->where('service_id',request()->service_id)->get();

        }
            return response()->json($requetes, 200);

    }
    public function getRejectedRequete()
    {

        $requetes=[];

        if (request()->service_id) {
        $idUser=Auth::id();
        $requetes=Requete::with(['files','reponses','parcours','affectation'])->where('status',2)->whereHas('affectations', function($q) use($idUser) {
            $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
            })->where('service_id',request()->service_id)->get();

        }
            return response()->json($requetes, 200);
    }
    public function getValidatedRequete()
    {

        $requetes=[];

        if (request()->service_id) {

        $idUser=Auth::id();
        $requetes=Requete::with(['files','reponses','parcours','affectation'])->where('status',4)->whereHas('affectations', function($q) use($idUser) {
            $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
            })->where('service_id',request()->service_id)->get();

        }
            return response()->json($requetes, 200);
    }

    public function getFinishedRequete()
    {

        $requetes=[];

        if (request()->service_id) {
        $role=Auth::user()->roles()->first()->name;
        if ($role=="dfea") {
            $idUser=Auth::id();
            $requetes=Requete::with(['files','reponses','parcours','affectation','TypeCape',])->where('status',7)->where('session_id',null)->where('service_id',request()->service_id)->get();
                return response()->json($requetes, 200);
        }else  if ($role=="cps"){
            $idUser=Auth::id();
            $requetes=Requete::with(['files','reponses','parcours','affectation'])->where('status',5)->whereHas('affectations', function($q) use($idUser) {
                $q->where('user_down',"=", $idUser)->where('isLast',"=", true);
                })->where('service_id',request()->service_id)->get();
                return response()->json($requetes, 200);
        }

    }
        return response()->json([], 200);

    }
    public function getAdmissibleRequete()
    {

            $requetes=Requete::with(['files','reponses','parcours','affectation'])->where('status',7)->where("session_id",null)->get();
                return response()->json($requetes, 200);
        

    }
    
    public function finishStore1(Request $request)
    {

        $code=$request->code;
        $datas=$request->all();
        $requete=Requete::where('code',$code)->first();
        if ($request->file('file')) {
            $filename= FileStorage::setFile("doc_store",$request->file('file'),$code,time());
            if ($requete->has_cps_file) {
                $reqFile=RequeteFile::where('reference',"Enquête sociale")->where('requete_id',$requete->id)->first();
                $reqFile->update([
                    "filename"=>$filename,
                ]);           
             }else {
                RequeteFile::create([
                    "type"=>"PDF",
                    "reference"=>"Enquête sociale",
                    "filename"=>$filename,
                    "level"=>1,
                    "file_id"=>null,
                    "requete_id"=>$requete->id,
                ]);
            }
          
            unset($datas['file']);

        }
        Parcours::create([
            'libelle'=>"Enregistrement de l'enquête sociale ",
            'requete_id'=>$requete->id,
            'user_id'=>Auth::id()
        ]);

        $requete->update(array_merge($datas,[
            'has_cps_file'=>true,
            'status'=>$requete->has_physical_deposit?5:$requete->status
        ]));

      
        return response()->json([
            "success"=>true,
            "message"=>"Fichier enquête sociale enregistré",
            "data"=>null
        ], 200);

    }

   /* 
    public function finishStore2(Request $request)
    {
        if($request->has('code')) {
            try {
                $requete = Requete::where('code', $request->input('code'))->first();

                $directoryPath = public_path('docs/' . $requete->code);
                $filePattern = $directoryPath . '/*recepisse_depot_physique*';

                $files = glob($filePattern);

                if (!empty($files)) {
                    $filePath = $files[0];
                } else {
                    return response()->json([
                        "success" => false,
                        "message" => "Le fichier récépissé n'a pas été trouvé",
                        "data" => null
                    ], 200);
                }

                $mailResult = Mailer::sendSimpleWithFile(
                    "emails.deposit_recepisse",
                    [
                        "name" => $requete->name
                    ],
                    "Récépissé de dépôt physique",
                    $requete->name_promoter,
                    $requete->email,
                    [$filePath]
                );

                // Vérifier le statut de l'envoi du mail
                if (!$mailResult['success']) {
                    return response()->json([
                        "success" => false,
                        "message" => "Échec de l'envoi du récépissé !",
                        "data" => null
                    ], 200);
                }

                return response()->json([
                    "success" => true,
                    "message" => 'Récépissé envoyé !',
                    "data" => null
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    "success" => false,
                    "message" => $e->getMessage(),
                    "data" => null
                ], 200);
            }
        } else {
            try {
                $requete = Requete::where('code', $request->code)->first();
                $datas = $request->all();
                $code = $request->code;
                $datas['date_depositor'] = date_create($datas['date_depositor']);
                unset($datas['code']);

                $recFile = time() . "recepisse_depot_physique_preview.pdf";
                $filePath = public_path('docs/' . $code . "/" . $recFile);
                $cape = $requete;
                $cape->district->municipality->departement;
                $cape->district->cps;
                $token = Str::uuid();
                $datas['status'] = $requete->has_cps_file ? 5 : $requete->status;

                $qrcode = QrCode::size(200)->generate(env('APP_FRONT_URL') . '/verify-document/' . $token);
                $decret = "";
                if (strtolower($requete->service?->name) == "cape") {
                    $decret = "Dans le but de se conformer aux dispositions du décret N° 2022-072  du 09 février 2022 fixant les modalités de création, d'organisation et de fonctionnement des centres d'accueil et de protection de l'enfant en République du Bénin";
                } else {
                    $decret = "Dans le but de se conformer aux dispositions du décret N° 2023-291  du 31 MAI 2023 fixant les modalités de création, d'organisation et de fonctionnement des garderies d'enfants en République du Bénin";
                }

                Pdf::loadView('emails.deposit_receipt_pj', [
                    "decret" => $decret,
                    "cape" => $cape,
                    "token" => $token,
                    "qrcode" => $qrcode,
                ])->save($filePath);

                RequeteFile::create([
                    "type" => "PDF",
                    "reference" => "Récépissé du dépôt de physique",
                    "filename" => $recFile,
                    "level" => 1,
                    "file_id" => null,
                    "token" => $token,
                    "delivery_by" => Auth::id(),
                    "requete_id" => $requete->id,
                ]);

                Parcours::create([
                    'libelle' => "Enregistrement des données du dépôt physique ",
                    'requete_id' => $requete->id,
                    'user_id' => Auth::id()
                ]);



                $mailResult = Mailer::sendSimpleWithFile(
                    "emails.deposit_recepisse",
                    [
                        "name" => $requete->name
                    ],
                    "Récépissé de dépôt physique",
                    $requete->name_promoter,
                    $requete->email,
                    [$filePath]
                );

                if (!$mailResult['success']) {
                    return response()->json([
                        "success" => false,
                        "message" => "Échec de l'envoi du mail : " . $mailResult['message'],
                        "data" => null
                    ], 200);
                }

                $requete->update(array_merge($datas, [
                    'has_physical_deposit' => true,
                    'receipt_preview' => $recFile
                ]));

                return response()->json([
                    "success" => true,
                    "message" => "Dépôt physique enregistré et récépissé envoyé",
                    "data" => [
                        "file" => $recFile
                    ]
                ], 200);

            } catch (\Exception  $e) {
                return response()->json([
                    "success" => false,
                    "message" => $e->getMessage(),
                    "data" => null
                ]);
            }
        }
    }
    */

  public function finishStore2(Request $request)
    {

        if ($request->has('codeForRecepisse')) {
            try {
                $requete = Requete::where('code', $request->input('codeForRecepisse'))->first();

                $directoryPath = public_path('docs/' . $requete->code);
                $filePattern = $directoryPath . '/' . $requete->receipt_preview;

                $files = glob($filePattern);

                if (!empty($files)) {
                    $filePath = $files[0];
                } else {
                    return response()->json([
                        "success" => false,
                        "message" => "Le fichier récépissé n'a pas été trouvé",
                        "data" => null
                    ], 200);
                }

                $emails = [
                    [
                        'email' => $requete->email,
                        'name' => $requete->name
                    ],
                    [
                        'email' => $requete->email_pomoter,
                        'name' => 'Monsieur/Madame Promotteur du centre ' . $requete->name_pomoter
                    ],
                    [
                        'email' => $requete->email_chief,
                        'name' => 'Monsieur/Madame Directeur du centre ' . $requete->name_chief
                    ],
                ];

                $uniqueEmails = [];
                $seenEmails = [];

                foreach ($emails as $email) {
                    if (!in_array($email['email'], $seenEmails)) {
                        $uniqueEmails[] = $email;
                        $seenEmails[] = $email['email'];
                    }
                }

                $emails = $uniqueEmails;

                $allMailsSent = true;

                foreach ($emails as $item) {
                    $mailResult = Mailer::sendSimpleWithFile(
                        "emails.deposit_recepisse",
                        [
                            "name" => $item['name'],
                        ],
                        "Récépissé de dépôt physique",
                        $item['name'],
                         $item['email'],
                        //'alexiskatel92@gmail.com',
                        [$filePath],
                    );

                    if (!$mailResult) {
                        $allMailsSent = false;
                        Log::error('Échec d\'envoi à ' . $item['email']);
                    }
                }

                // Condition pour le retour de réponse
                if ($allMailsSent) {
                    return response()->json([
                        "success" => true,
                        "message" => 'Récépissé envoyé !',
                        "data" => null
                    ], 200);
                } else {
                    return response()->json([
                        "success" => false,
                        "message" => 'Échec de l\'envoi du récépissé',
                        "data" => null
                    ], 500);
                }

            } catch (\Exception $e) {
                return response()->json([
                    "success" => false,
                    "message" => $e->getMessage(),
                    "data" => null
                ], 500);
            }
        } else {

           try {
                $requete = Requete::where('code', $request->code)->first();
                $datas = $request->all();
                $code = $request->code;
                $datas['date_depositor'] = date_create($datas['date_depositor']);
                unset($datas['code']);

                $recFile = time() . "recepisse_depot_physique_preview.pdf";
                $filePath = public_path('docs/' . $code . "/" . $recFile);
                $cape = $requete;
                $cape->district->municipality->departement;
                $cape->district->cps;
                $token = Str::uuid();
                $datas['status'] = $requete->has_cps_file ? 5 : $requete->status;

                $qrcode = QrCode::size(200)->generate(env('APP_FRONT_URL') . '/verify-document/' . $token);
                $decret = "";
                if (strtolower($requete->service?->name) == "cape") {
                    $decret = "Dans le but de se conformer aux dispositions du décret N° 2022-072  du 09 février 2022 fixant les modalités de création, d'organisation et de fonctionnement des centres d'accueil et de protection de l'enfant en République du Bénin";
                } else {
                    $decret = "Dans le but de se conformer aux dispositions du décret N° 2023-291  du 31 MAI 2023 fixant les modalités de création, d'organisation et de fonctionnement des garderies d'enfants en République du Bénin";
                }

                Pdf::loadView('emails.deposit_receipt_pj', [
                    "decret" => $decret,
                    "cape" => $cape,
                    "token" => $token,
                    "qrcode" => $qrcode,
                ])->save($filePath);

                RequeteFile::create([
                    "type" => "PDF",
                    "reference" => "Récépissé du dépôt de physique",
                    "filename" => $recFile,
                    "level" => 1,
                    "file_id" => null,
                    "token" => $token,
                    "delivery_by" => Auth::id(),
                    "requete_id" => $requete->id,
                ]);

                Parcours::create([
                    'libelle' => "Enregistrement des données du dépôt physique ",
                    'requete_id' => $requete->id,
                    'user_id' => Auth::id()
                ]);



                $emails = [
                    [
                        'email' => $requete->email,
                        'name' => $requete->name
                    ],
                    [
                        'email' => $requete->email_pomoter,
                        'name' => 'Monsieur/Madame Promotteur du centre ' . $requete->name_pomoter
                    ],
                    [
                        'email' => $requete->email_chief,
                        'name' => 'Monsieur/Madame Directeur du centre ' . $requete->name_chief
                    ],
                ];

                $uniqueEmails = [];
                $seenEmails = [];

                foreach ($emails as $email) {
                    if (!in_array($email['email'], $seenEmails)) {
                        $uniqueEmails[] = $email;
                        $seenEmails[] = $email['email'];
                    }
                }

                $emails = $uniqueEmails;

                $allMailsSent = true;

                foreach ($emails as $item) {
                    $mailResult = Mailer::sendSimpleWithFile(
                        "emails.deposit_recepisse",
                        [
                            "name" => $item['name'],
                        ],
                        "Récépissé de dépôt physique",
                        $item['name'],
                        $item['email'],
                        [$filePath],
                    );

                    if (!$mailResult) {
                        $allMailsSent = false;
                        Log::error('Échec d\'envoi à ' . $item['email']);
                    }
                }

                // Condition pour le retour de réponse
                if ($allMailsSent) {
                    Log::info('Récépissé envoyé !');

                } else {
                    Log::error('Échec de l\'envoi du récépissé');
                }

                if (!$mailResult) {
                    Log::error('Échec de l\'envoi du récépissé');
                }

                $requete->update(array_merge($datas, [
                    'has_physical_deposit' => true,
                    'receipt_preview' => $recFile
                ]));

                return response()->json([
                    "success" => true,
                    "message" => "Dépôt physique enregistré et récépissé envoyé",
                    "data" => [
                        "file" => $recFile
                    ]
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    "success" => false,
                    "message" => $e->getMessage(),
                    "data" => null
                ],500);
            }
        }
    }

    public function transUp(Request $request)
    {
        $requete=Requete::where('code',$request->code)->first();
        $message="";
        $status=$requete->status;
        if ($requete->status ==5 || $requete->affectation->sens == -1) {
            if (Auth::user()->roles()->first()->name=="cps") {
                $role=Role::where('name','ddasm')->first();
                $user=User::role($role)->where("department_id",$requete?->district?->municipality?->department?->id)->first();
                if ($user == null) {
                    return response()->json([
                        "message"=>"Veuillez contacter l'administrateur, compte Ddasm son actif"
                    ], 500);
                }
                $message="Compte DDASM non actif veuillez contacter l'administrateur";
                $libelle="Dossier validé transmis au DDASM";
                $status=5;
            }else{
                $role=Role::where('name','dfea')->first();
                $user=User::role($role)->first();
                $message="Compte DDASM non actif veuillez contacter l'administrateur";
                $libelle="Dossier validé transmis au DDASM";
                $status=6;
            }
      
        }
        if ($requete->status ==6) {
            $role=Role::where('name','dfea')->first();
            $user=User::role($role)->first();
            $message="Compte DFEA non actif veuillez contacter l'administrateur";
            $libelle="Dossier recevable pour inscription";
            $status=7;


        }
      
        if ($user) {
     
            Response::create([
                "user_id"=> Auth::id(),
                "observation"=> $request->observation,
                "requete_id"=> $requete->id
            ]);

        $requete->affectation->update(['isLast'=>false]);

        Affectation::create([
            "user_up"=> Auth::id(),
            "user_down"=> $user->id,
            "isLast"=>true,
            "requete_id"=> $requete->id
        ]);
        $requete->update(['status'=>$status]);
        Parcours::create([
            'libelle'=>$libelle,
            'requete_id'=>$requete->id,
            'user_id'=>Auth::id(),
        ]);  
    
        return response()->json([], 200);
        }else{
            Parcours::create([
                'libelle'=>$libelle,
                'requete_id'=>$requete->id,
                'user_id'=>Auth::id(),
            ]);  
        
            return response()->json([
                "success"=>true,
                "message"=>$message,
                "data"=>null
            ],200);
        }
       

    }
    public function transDown(Request $request)
    {
        $requete=Requete::where('code',$request->code)->first();
        $message="";
        $status=$requete->status;
        if ($requete->status ==5) {
                $status=4;
        }
        if ($requete->status ==6) {
            $status=5;
        }
        $user_down= $requete->affectation->user_up;
        $requete->affectation->update(['isLast'=>false]);
        Affectation::create([
            "user_up"=> Auth::id(),
            "user_down"=> $user_down,
            "isLast"=>true,
            "sens"=>-1,
            "instruction"=>$request->motif,
            "requete_id"=> $requete->id
        ]);
        $requete->update(['status'=>$status]);
        Parcours::create([
            'libelle'=>"Retour du dossier pour correction",
            'requete_id'=>$requete->id,
            'user_id'=>Auth::id(),
        ]);  
    
        return response()->json([], 200);
        
       

    }


public function inviteStore(Request $request)
    {
        //try {
            $requete = Requete::find($request->id);

            $checkFileTreatedValid = RequeteFile::where('requete_id', $requete->id)->where('is_treated', true)->where('is_valid', false)->where('file_id', "!=", null)
                ->orWhere('requete_id', $requete->id)->where('is_treated', false)->where('file_id', "!=", null)->get();
            if ($checkFileTreatedValid->count() > 0) {
                return response()->json([
                    "success" => false,
                    "message" => "Tous les documents n'ont pas été validés",
                    "data" => null
                ], 500);
            }

            $mailResult = Mailer::sendSimple(
                "emails.success",
                [
                    "cps" => $requete?->district?->cps?->name,
                    "date_meeting" => $request->date_meeting,
                ],
                "Invitation au dépôt du dossier physique",
                $requete->name_pomoter,
                $requete->email
            );



            Parcours::create([
                'delay' => $this->getDelay($requete->id),
                'libelle' => "Envoi de mail d'invitation au CAPE",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);


            $requete->update(['status' => 4]);

            Agenda::create([
                'invite_date' => date_create($request->date_meeting),
                'cps_id' => $requete?->district?->cps?->id,
                'status' => $requete?->id,
            ]);

            return response()->json([
                "success" => true,
                "message" => "Invitation envoyé avec succès",
                "data" => null
            ], 200);
        // } catch (\Exception $e) {
        //     Log::error($e);
        //     return response()->json([
        //         "success" => false,
        //         "message" => $e->getMessage(),
        //         "data" => null
        //     ], 200);
        // }
    }


     public function setDecision(Request $request)
    {

        $requete=Requete::find($request->id);
        $avis=[];

        if ($request->decision ==0) {
            $i=0;
                foreach ($requete->files as $value) {
                    foreach ($value->avis as $key) {
                        $avis[$i]=$key;

                    $i++;
                    }
                   
                }
                Parcours::create([
                    'libelle'=>"Envoi des mails de rejet au CAPE",
                    'requete_id'=>$requete->id,
                    'user_id'=>Auth::id(),
                ]); 
          
            Mailer::sendSimple(
                "emails.rejected",
                [
                    "observation"=>$request->observation,
                    "motifs"=>$avis
                ],
                "Décision du conseil",
                $requete->name_promoter,
                $requete->email);
          }else {
            foreach (json_decode($request->referals) as $value) {
                    Referal::create([
                        "libelle"=>$value->observation,
                        "requete_id"=>$request->id
                    ]);
                
            }    
            Parcours::create([
                'libelle'=>"Enregistrement des recommandations issues de la session",
                'requete_id'=>$requete->id,
                'user_id'=>Auth::id(),
            ]); 
         }

         $requete->update([
            "has_agreemant"=>$request->decision,
            "observation"=>$request->observation,
            "note"=>$request->note
        ]);      

  
          return response()->json([], 200);

}



    public function authorized()
    {

        $session=Session::where('is_active',false)->orderBy("id","desc")->first();
        $requetes=Requete::where('session_id', $session->id)->where('is_authorized',true)->get();
        foreach ($requetes as $value) {
                // $cape=Cape::create([
                //     "requete_id"=>$value->id,
                //     "status"=>1
                // ]);

                $refs=Referal::where('requete_id',$value->id)->get();
                
                // foreach ($refs as  $ref) {
                //     $ref->update(['cape_id'=>$cape->id]);
                // }

                $user=$value?->promoter?->user;

                $recFile=time()."agrement.pdf";
                $filePath=public_path('docs/'.$value->code."/".$recFile);
                RequeteFile::create([
                    "type"=>"PDF",
                    "reference"=>"Agrément d'autorisation",
                    "filename"=>$recFile,
                    "level"=>1,
                    "file_id"=>null,
                    "requete_id"=>$value->id,
                ]);
        
                Pdf::loadView('emails.agrement_pj', [
                  
                    "name"=>$value->name
                ])->save($filePath);
                Mailer::sendSimpleWithFile(
                    "emails.cape_account",
                    [
                        "name"=>$value->name,
                        "cape"=>$user,
                    ],
                    "Décision finale",
                    $user->lastname. " ". $user->firstname,
                    $user->email,
                    [$filePath]
                    );
                    Parcours::create([
                        'libelle'=>"Délivrance d'autorisation au CAPE",
                        'requete_id'=>$value->id,
                        'user_id'=>Auth::id(),
                    ]); 
                    $value->update([
                        "can_closed"=>true,
                        "is_validated"=>true
                    ]);
            

            
           
        }

        return response()->json([
            "success"=>true,
            "message"=>"Capes autorisés avec succès",
            "data"=>$requetes
        ], 200);

    }

    public function getDecision()
    {

        $session=Session::where('is_active',false)->orderBy("id","desc")->first();
        $requetes=Requete::where('session_id', $session->id)->where('can_closed', false)->where('status',"!=",8)->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des capes",
            "data"=>$requetes
        ], 200);

    }

    

    public function setStatus(Request $request)
    {
        if ($request->decision == 1) {
            foreach (json_decode($request->requetes) as $value) {
                Requete::find($value->id)->update([
                    'is_authorized'=>$request->decision
                    
                ]);
                Parcours::create([
                    'libelle'=>"Enregistrement de la décision",
                    'requete_id'=>$value->id,
                    'user_id'=>Auth::id(),
                ]); 
            }
        }else {
            foreach (json_decode($request->requetes) as $value) {
                $req=Requete::find($value->id);
                foreach (json_decode($request->decisions) as $key) {
                    Referal::create([
                        "libelle"=>$key->observation,
                        "requete_id"=>$req->id
                    ]);
                }
                $req->update([
                    'is_authorized'=>$request->decision,
                    'final_observation'=>$request->final_observation
                ]);
                Parcours::create([
                    'libelle'=>"Enregistrement de la décision",
                    'requete_id'=>$value->id,
                    'user_id'=>Auth::id(),
                ]); 
            }
        }
      

        return response()->json([
            "success"=>true,
            "message"=>"Décision pour Cape",
            "data"=>[]
        ], 200);
    }



   function getListForPublic($serviceId){
        $requetes=Requete::with(['TypeCape','service','Cape','district.municipality.department','district.cps'])->where('is_authorized',true)->where('service_id',$serviceId)->where('is_validated',true)->where("session_id","!=",null)
        ->orWhere("status",8)->where('is_validated',true)->get();
        return response()->json($requetes, 200);
    }


    public function getPendingValidation()
    {
       // $requetes=Requete::with(['TypeCape','service','Cape','district.municipality.department','district.cps'])->where('is_authorized',true)->where('is_validated',false)->where('has_agreemant',true)->where("session_id",null)->get();
        $requetes=Requete::with(['TypeCape','service','Cape','district.municipality.department','district.cps'])->where('status',8)->get();
        return response()->json([
            "success"=>true,
            "message"=>"En attente de validation",
            "data"=>$requetes
        ], 200);
    }

    public function setStatus2(Request $request)
    {
        $req=Requete::find($request->id);
        $req->update([
            'is_validated'=>$request->decision,
        ]);
        return response()->json([
            "success"=>true,
            "message"=>"Décision pour Cape",
            "data"=>[]
        ], 200);
    }



   static public function generateUniqueCode($service)
{

    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersNumber = strlen($characters);
    $codeLength = 6;
    $prefixe = $service->name.'-';
    $code = '';

    while (strlen($code) < 6) {
        $position = rand(0, $charactersNumber - 1);
        $character = $characters[$position];
        $code =$code.$character;
    }

    if (Requete::where('code', $prefixe.$code)->exists()) {
        return  $this->generateUniqueCode($service);
    }

    return $prefixe.$code;

}


    public function setFileTreatment(Request $request)
    {
        $reqFile=RequeteFile::find($request->id);
        $reqFile->update([
            "is_treated"=>true,
            "observation"=>$request->observation,
            "is_valid"=>$request->is_valid
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Traitement de fichier effectué",
            "data"=>[]
        ], 200);
    }

    public function getDelay($d)
    {
        $lp=Requete::find($d)->lastParcours;
        $now = time(); // or your date as well
        $your_date = strtotime($lp->created_at);
        $datediff = $now - $your_date;
       return round($datediff / (60 * 60 * 24));
    }



    function destroy ($id){
        
        $requete= Requete::find($id);

        if($requete->Cape == null){
            if($requete->files?->count()>0){
                foreach ($requete->files as $value) {
                    if ($value->avis?->count()!=0) {
                       foreach ($value->avis as $value2) {
                        $value2->delete();
                    }
                    }
                    $value->delete();
                }
            }
          
            if($requete->files2?->count()>0){
                foreach ($requete->files2 as $value) {
                    $value->delete();
                }
            }
          
            if($requete->reponses?->count()>0){
                foreach ($requete->reponses as $value) {
                    $value->delete();
                }
            }
          
            if($requete->affectations?->count()>0){
                foreach ($requete->affectations as $value) {
                    $value->delete();
                }
            }
            if($requete->parcours?->count()>0){
                foreach ($requete->parcours as $value) {
                    $value->delete();
                }
            }
            if($requete->RequeteTypeGarderies?->count()>0){
                foreach ($requete->RequeteTypeGarderies as $value) {
                    $value->delete();
                }
            }
          
            $requete->delete();

            return response()->json([
                "success"=>true,
                "message"=>"Suppression de l'élément",
                "data"=>null
            ], 200);
        }else {
            return response()->json([
                "success"=>true,
                "message"=>"Impossible de supprimer l'élément",
                "data"=>null
            ], 500);
        }
       
      
    }
}
