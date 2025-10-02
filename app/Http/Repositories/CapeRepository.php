<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Cape;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class CapeRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Cape
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Cape::class);
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
        // Retour direct de toutes les capes avec la relation requete.TypeCape
        $capes = Cape::with(['requete.TypeCape'])->get();
        return $capes;
    }


    public function getAllAuthorized()
    {
        $role = Auth::user()->roles()->first()->name;
        $req = Cape::query(); // Requête de base

        switch ($role) {
            case 'cps':
                $departDistricts = Auth::user()->cps->districts->pluck('id');

                $req = Cape::with([
                    'requete.TypeCape',
                    'controls' => function($q) {
                        $q->where("is_valid", true)->with('TypeControl');
                    },
                    'myControls' => function($q) {
                        $q->where("user_id", Auth::id())->with('TypeControl');
                    },
                    'transmittedControls' => function($q) {
                        $q->with('TypeControl')->whereHas("transmissions", function($qu) {
                            $qu->where("user_id", "!=", Auth::id())
                            ->where("isLast", true)
                            ->where("user_down", Auth::id());
                        });
                    },
                ])->whereHas('requete', function($q) use ($departDistricts) {
                    $q->whereIn('district_id', $departDistricts)
                    ->where('is_authorized', true);
                });
                break;

            case 'ddasm':
                $departDistricts = [];
                $depart = Department::find(Auth::user()->department_id);
                foreach ($depart->municipalities as $municipality) {
                    foreach ($municipality->districts as $district) {
                        $departDistricts[] = $district->id;
                    }
                }

                $req = Cape::with(['requete.TypeCape'])
                    ->whereHas('requete', function($q) use ($departDistricts) {
                        $q->whereIn('district_id', $departDistricts)
                        ->where('is_authorized', true);
                    });
                break;

            case 'dfea':
            case 'ministre':
                $req = Cape::with(['requete.TypeCape']);
                break;

            default:
                $req = Cape::query(); // Requête vide si rôle non reconnu
                break;
        }

        // Récupération finale de toutes les capes correspondant à la requête
        $capes = $req->get();

        return $capes;
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
    public function makeStore(Request $request): Cape
    {
        $datas = $request->all();
        $datas['user_id'] = Auth::id();

        $cape = new Cape($datas);
        $cape->save();

        return $cape;

    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Cape
    {
        $datas = $request->all();

        $cape = Cape::findOrFail($id);
        $cape->update($datas);

    return $cape;
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
        $cape = Cape::findOrFail($id);
        $cape->update(['is_active' => $status]);

        return $cape;
    }

    public function show($id)
    {
        $cape = Cape::with([
            'requete.referals.controls',
            'requete.TypeCape',
            'requete.files.file',
            'residents',
            'staffs'
        ])->findOrFail($id);

        return $cape;
    }

    public function downloadImportFile(Request $request)
    {
        set_time_limit(0);

        $file = $request->file('file');
        if (!$file) {
            abort(400, "Fichier non fourni");
        }

        $inputFileType = $file->getClientOriginalExtension();
        $reader = IOFactory::createReader(ucfirst($inputFileType));
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $sheet->getHighestRow();

            for ($row = 3; $row <= $highestRow; $row++) {
                $municipalityName = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                $municipality = Municipality::where('name', $municipalityName)->first();
                if ($municipality) {
                    $municipality->update([
                        "mayor_name" => $sheet->getCellByColumnAndRow(3, $row)->getValue(),
                        "mayor_phone" => $sheet->getCellByColumnAndRow(4, $row)->getValue(),
                        "mayor_political_party" => $sheet->getCellByColumnAndRow(5, $row)->getValue(),
                        "mayor_job" => $sheet->getCellByColumnAndRow(6, $row)->getValue(),
                    ]);
                }
            }
        }

        return "Importation réussie";
    }

    public function setImportProjectImportFile(Request $request)
    {
        set_time_limit(0);

        $file = $request->file('file');
        if (!$file) {
            abort(400, "Fichier non fourni");
        }

        $inputFileType = $file->getClientOriginalExtension();
        $reader = IOFactory::createReader(ucfirst($inputFileType));
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $sheet->getHighestRow();

            for ($row = 3; $row <= $highestRow; $row++) {
                $typeCapeName = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                $districtName = $sheet->getCellByColumnAndRow(2, $row)->getValue();

                $typeCape = TypeCape::where('name', $typeCapeName)->first();
                $district = District::where('name', $districtName)->first();

                if ($typeCape && $district) {
                    $capeName = $sheet->getCellByColumnAndRow(3, $row)->getValue();
                    $cape = Requete::firstOrNew(['name' => $capeName]);
                    $cape->fill([
                        "code" => $cape->code ?? Str::uuid(),
                        "name" => $capeName,
                        "type_cape_id" => $typeCape->id,
                        "district_id" => $district->id,
                        "status" => $cape->status ?? 8,
                        // Ajouter ici les autres champs en remplissant depuis le fichier
                    ]);
                    $cape->save();
                }
            }
        }

        return "Importation réussie";
    }

    public function buildSelect($sheet, $cell, $value, $data)
    {
        $configs = implode(", ", $data->pluck('name')->toArray());
        $sheet->setCellValue($cell, $value);

        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Entrée erronée')
                ->setError('Valeur non retrouvée')
                ->setPromptTitle('Choisissez un élément')
                ->setPrompt('Veuillez sélectionner une valeur dans la liste')
                ->setFormula1('"' . $configs . '"');

        return $sheet;
    }

}
