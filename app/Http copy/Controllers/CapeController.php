<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cape;
use App\Models\Department;
use App\Models\Municipality;
use App\Models\District;
use App\Models\TypeCape;
use App\Models\User;
use App\Models\Requete;

use Auth,Response,Str;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;


class CapeController extends Controller
{


    public function __construct() {
      
        $this->middleware('auth', ['except' => ['downloadImportFile']]);
    }
  

    public function getAllAuthorized()
    {
        $capes=[];

        if (request()->service_id) {

            $service_id=request()->service_id;

        $role=Auth::user()->roles()->first()->name;
        $capes=[];

        switch ($role) {
            case 'cps':
               // 'controls.TypeControl',
                $departDistricts=Auth::user()->cps->districts->pluck('id');
                $capes=Cape::with([
                    'requete.TypeCape',
                    'controls'=>function($q){$q->where("is_valid",true)->with('TypeControl');},
                    'myControls'=>function($q){$q->where("user_id",Auth::id())->with(['TypeControl'])->withCount('transmissions');},
                    'transmittedControls'=>function($q){
                        $q->with('TypeControl')->whereHas("transmissions",function($qu){
                        $qu->where("user_id","!=",Auth::id())->where("isLast",true)->where("user_down",Auth::id());
                    });},
                    
                    
                    ])->whereHas('requete',function($q)use($departDistricts,$service_id){
                    $q->whereIn('district_id',$departDistricts)->where('is_authorized',true)->where('service_id',$service_id);
                })->get();
            break;
            case 'ddasm':
                $departDistricts=[];
                $i=0;
                $depart=Department::find(Auth::user()->department_id);
                   foreach ($depart->municipalities as $key) {
                    foreach ($key->districts as $d) {
                        $departDistricts[$i]= $d->id;
                        $i++;
                     }
                   }
        
                   $capes=Cape::with([
                    'requete.TypeCape',
                    'controls'=>function($q){$q->where("is_valid",true)->with('TypeControl');},
                    'myControls'=>function($q){$q->where("user_id",Auth::id())->with(['TypeControl'])->withCount('transmissions');},
                    'transmittedControls'=>function($q){
                        $q->with('TypeControl')->whereHas("transmissions",function($qu){
                        $qu->where("user_id","!=",Auth::id())->where("isLast",true)->where("user_down",Auth::id());
                    });},
                    
                    
                    ])->whereHas('requete',function($q)use($departDistricts,$service_id){
                    $q->whereIn('district_id',$departDistricts)->where('is_authorized',true)->where('service_id',$service_id);
                })->get();
            break;
            case 'dfea':
                $capes=Cape::with(['requete.TypeCape'])->whereHas('requete',function($q)use($service_id){
                    $q->where('service_id',$service_id);
                })->get();
            break;
            case 'ministre':
                $capes=Cape::with(['requete.TypeCape'])->whereHas('requete',function($q)use($service_id){
                    $q->where('service_id',$service_id);
                })->get();
            break;
            default:

            break;
        }
      
    }
        return response()->json([
            "success"=>true,
            "message"=>"Liste des capes",
            "data"=>$capes
        ],200);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $capes=Cape::with(['requete.TypeCape'])->get();
        return response()->json([
            "success"=>true,
            "message"=>"Liste des capes",
            "data"=>$capes
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
        $district=District::find($request->district_id);
        if ($district->cps == null) {
            return response()->json([
                "success"=>true,
                "message"=>"Veuillez contacter l'administrateur!Arrondissement non classé",
                "data"=>null
            ],500);
        }
        $user=User::where('cps_id',$district->cps->id)->first();

        if ( $user== null) {
            return response()->json([
                "success"=>true,
                "message"=>"Veuillez contacter l'administrateur! Compte Cps inexistant",
                "data"=>null
            ],500);
        }
       // $code=Str::uuid();
       $code=RequeteController::generateUniqueCode();

        $requete=Requete::create([
            "code"=>$code,
            "name"=>$request->name,
            "type_cape_id"=>(int)$request->type_cape_id,
            "name_pomoter"=>$request->name_pomoter,
            "firstname_pomoter"=>$request->firstname_pomoter,
            "phone_pomoter"=>$request->phone_pomoter,
            "email_pomoter"=>$request->email_pomoter,
            "name_chief"=>$request->name_chief??$request->name_pomoter,
            "phone_chief"=>$request->phone_chief??$request->name_pomoter,
            "firstname_chief"=>$request->firstname_chief,
            "email_chief"=>$request->email_chief,
            "email"=>$request->email,
            "phone"=>$request->phone,
            "capacity"=>$request->capacity,
            "town"=>$request->town,
            "address"=>$request->address,
            "target"=>$request->targets,
            "status"=>8,
            "is_authorized"=>true,
            "has_agreemant"=>true,
            "status"=>8,
            "district_id"=> (int)$request->district_id
        ]);

        Cape::create([
            "requete_id"=>$requete->id,
            "status"=>1,
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un cape",
            "data"=>null
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

        $capes=Cape::with([
            'requete.referals.controls',
            'requete.TypeCape',
            'requete.files.file',
            'residents',
            'staffs'
            ])->where("id",$id)->first();
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un cape",
            "data"=>$capes
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
       $requete=Requete::find($id);
        $requete->update([
            "name"=>$request->name,
            "type_cape_id"=>(int)$request->type_cape_id,
            "name_pomoter"=>$request->name_pomoter,
            "firstname_pomoter"=>$request->firstname_pomoter,
            "phone_pomoter"=>$request->phone_pomoter,
            "email_pomoter"=>$request->email_pomoter,
            "name_chief"=>$request->name_chief??$request->name_pomoter,
            "phone_chief"=>$request->phone_chief??$request->name_pomoter,
            "firstname_chief"=>$request->firstname_chief,
            "email_chief"=>$request->email_chief,
            "email"=>$request->email,
            "phone"=>$request->phone,
            "capacity"=>$request->capacity,
            "town"=>$request->town,
            "address"=>$request->address,
            "target"=>$request->targets,
            "district_id"=> (int)$request->district_id
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>null
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
        $capes=Cape::find($id);
        $capes->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un cape",
            "data"=>null
        ],200);
    }
    public function setStatus($id,$status)
    {
        $capes=Cape::find($id);
        $capes->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
    }



    public function downloadImportFile()
    {
        $spreadsheet = new Spreadsheet();
        $isFirst=true;
        $departments=Department::orderBy('name','asc')->get();
        $municipalities=Municipality::orderBy('name','asc')->get();
        $districts=District::orderBy('name','asc')->get();
        $typeCapes=TypeCape::orderBy('name','asc')->get();

        if (
            $departments->count()>0 &&
            $municipalities->count()>0 &&
            $districts->count()>0 &&
            $typeCapes->count()>0             
            ) {
                $sheet =$isFirst? $spreadsheet->getActiveSheet(): $spreadsheet->createSheet();                  
                $sheet->setTitle(substr("Importation des capes autorisés",0,31));
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(30);
                $sheet->getColumnDimension('G')->setWidth(30);
                $sheet->getColumnDimension('H')->setWidth(30);
                $sheet->getColumnDimension('I')->setWidth(30);
                $sheet->getColumnDimension('J')->setWidth(30);
                $sheet->getColumnDimension('K')->setWidth(30);
                $sheet->getColumnDimension('L')->setWidth(30);
                $sheet->getColumnDimension('M')->setWidth(30);
                $sheet->getColumnDimension('N')->setWidth(30);
                $sheet->getColumnDimension('O')->setWidth(30);
                $sheet->getColumnDimension('P')->setWidth(30);
                $sheet->getColumnDimension('Q')->setWidth(30);
                $sheet->getColumnDimension('R')->setWidth(30);
                $sheet->getColumnDimension('S')->setWidth(30);
                $sheet->setCellValue("A1", 'Listes des CAPES disposant d\'agrément d\'exercice');
                $sheet->setCellValue("A2", 'Nom promoteur');
                $sheet->setCellValue("B2", 'Prénoms promoteur');
                $sheet->setCellValue("C2", 'Email promoteur');
                $sheet->setCellValue("D2", 'Contact promoteur');
                $sheet->setCellValue("E2", 'Nom directeur');
                $sheet->setCellValue("F2", 'Prénoms directeur');
                $sheet->setCellValue("G2", 'Email directeur');
                $sheet->setCellValue("H2", 'Contact directeur');
                $sheet->setCellValue("J2", 'Dénomination du centre');
                $sheet->setCellValue("K2", 'Capacité du centre');
                $sheet->setCellValue("L2", 'Email du centre');
                $sheet->setCellValue("M2", 'Contact du centre');
                $sheet->setCellValue("N2", 'Cibles accueillies');
                $sheet->setCellValue("P2", 'Quartier de ville / Village');
                $sheet->setCellValue("Q2", 'Adresse');

                $configs ="";
                $i = 0;
                $len = count($typeCapes);
                foreach ($typeCapes as $el) {
                     if ($i == $len - 1) {
                        $configs.=$el->name;
        
                    }else {
                        $configs.=$el->name.", ";
                    }
                    $i++;
                }
                $sheet->setCellValue("I2", 'Type de CAPE' );
                $objValidation = $sheet->getCell("I2")->getDataValidation();
                $objValidation->setType(DataValidation::TYPE_LIST);
                $objValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $objValidation->setAllowBlank(false);
                $objValidation->setShowInputMessage(true);
                $objValidation->setShowErrorMessage(true);
                $objValidation->setShowDropDown(true);
                $objValidation->setErrorTitle('Entrée erronée');
                $objValidation->setError('Valeur non retrouvée');
                $objValidation->setPromptTitle('Choisissez un élément');
                $objValidation->setPrompt('Veuillez sélectionner une valeur dans la liste');
                $objValidation->setFormula1('"' . $configs . '"');


          
               
               // $sheet=$this->buildSelect($sheet,"I2",'Type de CAPE',$typeCapes);
              //  $sheet=$this->buildSelect($sheet,"O2",'Arrondissement',$districts);
            
               

                    $type="xlsx";
                    $fileName = date("d_m_Y_h_i_s_")."cape_autorisés.".$type;
                    if($type == 'xlsx') {
                    $writer = new Xlsx($spreadsheet);
                    } else if($type == 'xls') {
                    $writer = new Xls($spreadsheet);
                    }
                    $writer->save("exports/".$fileName);
                    header("Content-Type: application/vnd.ms-excel");
                    return Response::download(public_path("exports/".$fileName));
                    
             
        }else {
            echo "Certaines données comme les départements, communes , arrondissement et type cape sont indispensables pour l'importation des données";
            return;
        }
       
    }


    public function buildSelect($sheet,$key,$value,$data)
    {

        $configs ="";
        $i = 0;
        $len = count($data);
        foreach ($data as $el) {
             if ($i == $len - 1) {
                $configs.=$el->name;

            }else {
                $configs.=$el->name.", ";
            }
            $i++;
        }
        $sheet->setCellValue($key, $value, );
        $objValidation = $sheet->getCell($key)->getDataValidation();
        $objValidation->setType(DataValidation::TYPE_LIST);
        $objValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(false);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setErrorTitle('Entrée erronée');
        $objValidation->setError('Valeur non retrouvée');
        $objValidation->setPromptTitle('Choisissez un élément');
        $objValidation->setPrompt('Veuillez sélectionner une valeur dans la liste');
        $objValidation->setFormula1('"' . $configs . '"');

        return $sheet;
    }


    public function import(Request $request)
    {

        set_time_limit(0);
        $file=$request->file('file');
        $inputFileType=$file?->getClientOriginalExtension();

        $reader = IOFactory::createReader(Str::title($inputFileType));
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load($file);
        $all = $spreadsheet->getSheetNames();
        foreach ($all as $value) {
            $currentSheet=$spreadsheet->getSheetByName($value);
            $highestRow = $currentSheet->getHighestRow(); 
            $highestColumn = $currentSheet->getHighestColumn(); 
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5


                        for ($row = 3; $row <= $highestRow; ++$row) {

                            $checkTypeCape= TypeCape::where('name',$currentSheet->getCellByColumnAndRow(9, $row)->getValue()??null)->first();
                            $checkDistrict= District::where('name',$currentSheet->getCellByColumnAndRow(15, $row)->getValue()??null)->first();

                            if ($checkTypeCape && $checkDepartment && $checkMunicipality && $checkDistrict) {
                                $checkCape=Requete::where('name',$currentSheet->getCellByColumnAndRow(10, $row)->getValue()??null)->first();
                                if ($checkCape) {
                                        $checkCape->update([
                                            "name"=>$currentSheet->getCellByColumnAndRow(10, $row)->getValue()??null,
                                            "type_cape_id"=>$checkTypeCape->id,
                                            "name_pomoter"=>$currentSheet->getCellByColumnAndRow(1, $row)->getValue()??null,
                                            "firstname_pomoter"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                            "phone_pomoter"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                            "email_pomoter"=>$currentSheet->getCellByColumnAndRow(4, $row)->getValue()??null,
                                            "name_chief"=>$currentSheet->getCellByColumnAndRow(5, $row)->getValue()??null,
                                            "phone_chief"=>$currentSheet->getCellByColumnAndRow(8, $row)->getValue()??null,
                                            "firstname_chief"=>$currentSheet->getCellByColumnAndRow(6, $row)->getValue()??null,
                                            "email_chief"=>$currentSheet->getCellByColumnAndRow(7, $row)->getValue()??null,
                                            "email"=>$currentSheet->getCellByColumnAndRow(12, $row)->getValue()??null,
                                            "phone"=>$currentSheet->getCellByColumnAndRow(13, $row)->getValue()??null,
                                            "capacity"=>$currentSheet->getCellByColumnAndRow(11, $row)->getValue()??null,
                                            "town"=>$currentSheet->getCellByColumnAndRow(16, $row)->getValue()??null,
                                            "address"=>$currentSheet->getCellByColumnAndRow(17, $row)->getValue()??null,
                                            "status"=>8,
                                            "district_id"=>$checkDistrict->id,
                                    ]);
                                }else {
                                  $code=Str::uuid();

                                   $req=Requete::create([
                                    "code"=>$code,
                                    "name"=>$currentSheet->getCellByColumnAndRow(10, $row)->getValue()??null,
                                    "type_cape_id"=>$checkTypeCape->id,
                                    "name_pomoter"=>$currentSheet->getCellByColumnAndRow(1, $row)->getValue()??null,
                                    "firstname_pomoter"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "phone_pomoter"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                    "email_pomoter"=>$currentSheet->getCellByColumnAndRow(4, $row)->getValue()??null,
                                    "name_chief"=>$currentSheet->getCellByColumnAndRow(5, $row)->getValue()??null,
                                    "phone_chief"=>$currentSheet->getCellByColumnAndRow(8, $row)->getValue()??null,
                                    "firstname_chief"=>$currentSheet->getCellByColumnAndRow(6, $row)->getValue()??null,
                                    "email_chief"=>$currentSheet->getCellByColumnAndRow(7, $row)->getValue()??null,
                                    "email"=>$currentSheet->getCellByColumnAndRow(12, $row)->getValue()??null,
                                    "phone"=>$currentSheet->getCellByColumnAndRow(13, $row)->getValue()??null,
                                    "capacity"=>$currentSheet->getCellByColumnAndRow(11, $row)->getValue()??null,
                                    "town"=>$currentSheet->getCellByColumnAndRow(16, $row)->getValue()??null,
                                    "address"=>$currentSheet->getCellByColumnAndRow(17, $row)->getValue()??null,
                                    "status"=>8,
                                    "district_id"=>$checkDistrict->id,
                                   
                                   ]);
                                   CAPE::create(
                                    [
                                      "status"=>1,
                                      "requete_id"=>$req->id,
                                   
                                    ]
                                    );

                                
                                }
                            }
                           

                        }
        }




      return response()->json([
        "success"=>true,
        "message"=>"Importation réussie",
        "data"=>[
          
        ]
    ],200);  
       
    }


    public function generateUniqueCode()
    {
    
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $codeLength = 6;
        $prefixe = 'CAPE-';
        $code = '';
    
        while (strlen($code) < 6) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $prefixe.$code.$character;
        }
    
        if (Requete::where('code', $code)->exists()) {
            $this->generateUniqueCode();
        }
    
        return $code;
    
    }
}
