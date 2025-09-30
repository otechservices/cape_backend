<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cape;
use App\Models\Department;
use App\Models\Municipality;
use App\Models\District;
use App\Models\TypeCape;

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

        $role=Auth::user()->roles()->first()->name;
        $capes=[];

        switch ($role) {
            case 'cps':
               // 'controls.TypeControl',
                $departDistricts=Auth::user()->cps->districts->pluck('id');
                $capes=Cape::with([
                    'requete.TypeCape',
                    'controls'=>function($q){$q->where("is_valid",true)->with('TypeControl');},
                    'myControls'=>function($q){$q->where("user_id",Auth::id())->with('TypeControl');},
                    'transmittedControls'=>function($q){
                        $q->with('TypeControl')->whereHas("transmissions",function($qu){
                        $qu->where("user_id","!=",Auth::id())->where("isLast",true)->where("user_down",Auth::id());
                    });},
                    
                    
                    ])->whereHas('requete',function($q)use($departDistricts){
                    $q->whereIn('district_id',$departDistricts)->where('is_authorized',true);
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
        
                $capes=Cape::with(['requete.TypeCape'])->whereHas('requete',function($q)use($departDistricts){
                    $q->whereIn('district_id',$departDistricts)->where('is_authorized',true);
                })->get();
            break;
            case 'dfea':
                $capes=Cape::with(['requete.TypeCape'])->get();
            break;
            case 'ministre':
                $capes=Cape::with(['requete.TypeCape'])->get();
            break;
            default:

            break;
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
        $datas = $request->all();
        $datas ['user_id']=Auth::id();
        $capes=Cape::create($datas);

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un cape",
            "data"=>$capes
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
        $datas=$request->all();
       
        $capes=Cape::find($id);

        $capes->update($datas);

        $capes=Cape::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une péridiocité",
            "data"=>$capes
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

                            $municipalityName=$currentSheet->getCellByColumnAndRow(1, $row)->getValue()??null;
                            $municipality=Municipality::where('name',$municipalityName)->first();
                            if ($municipality) {
                                $municipality2=$municipality;

                                $municipality2->update([
                                    "mayor_name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                    "mayor_phone"=>$currentSheet->getCellByColumnAndRow(4, $row)->getValue()??null,
                                    "mayor_political_party"=>$currentSheet->getCellByColumnAndRow(5, $row)->getValue()??null,
                                    "mayor_job"=>$currentSheet->getCellByColumnAndRow(6, $row)->getValue()??null,
                                
                                ]);
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

    public function setImportProjectImportFile(Request $request)
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

                            $checkTypeCape= TypeCape::where('name',$currentSheet->getCellByColumnAndRow(1, $row)->getValue()??null)->first();
                            $checkDistrict= District::where('name',$currentSheet->getCellByColumnAndRow(1, $row)->getValue()??null)->first();

                            if ($checkTypeCape && $checkDepartment && $checkMunicipality && $checkDistrict) {
                                $checkCape=Requete::where('name',$currentSheet->getCellByColumnAndRow(1, $row)->getValue()??null)->first();
                                if ($checkCape) {
                                        $checkCape->update([
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                        "name"=>$currentSheet->getCellByColumnAndRow(3, $row)->getValue()??null,
                                    
                                    ]);
                                }else {
                                  $code=Str::uuid();

                                   $req=Requete::create([
                                    "code"=>$code,
                                    "name"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "type_cape_id"=>$checkTypeCape->id,
                                    "name_pomoter"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "firstname_pomoter"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "phone_pomoter"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "email_pomoter"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "name_chief"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "phone_chief"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "firstname_chief"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "email_chief"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "email"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "phone"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "capacity"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "town"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                    "status"=>8,
                                    "district_id"=>$currentSheet->getCellByColumnAndRow(2, $row)->getValue()??null,
                                   
                                   ]);
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

    public function buildSelect($sheet,$key,$value,$data)
    {

        $configs ="";
        foreach ($data as $el) {
            $configs.=$el->name.", ";
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
}
