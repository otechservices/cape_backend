<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\BackupControllerRepository;
use App\Http\Requests\BackupController\StoreBackupControllerRequest;
use App\Http\Requests\BackupController\UpdateBackupControllerRequest;
use App\Services\LogService;
use App\Utilities\Common;
use OpenApi\Attributes as OA;
use App\Utilities\ErrorMessage;


class BackupControllerController extends Controller
{
     /**
     * The BackupController repository being queried.
     *
     * @var BackupControllerRepository
     */
    protected $BackupControllerRepository;

    protected $ls;

    public function __construct(BackupControllerRepository $BackupControllerRepository, LogService $ls)
    {
        $this->BackupControllerRepository = $BackupControllerRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/backups",
     *      operationId="BackupController list",
     *      tags={"BackupController"},
     *       security={{"JWT":{}}},
     *      summary="Return BackupController data",
     *      description="Get all backups",
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Can be used for filtering data by name",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/BackupController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/BackupController")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function index(Request $request)
    {
        $message = 'Récupération de la liste des BackupController';

        try {
            $result = $this->BackupControllerRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    /** @OA\Post(
     *      path="/backups",
     *      operationId="BackupController store",
     *      tags={"BackupController"},
     *       security={{"JWT":{}}},
     *      summary="Store BackupController data",
     *      description="Create a new BackupController",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/BackupControllerCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/BackupController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/BackupController")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function store(StoreBackupControllerRequest $request)
    {
        $message = 'Enregistrement d\'un BackupController';

        try {
            $result = $this->BackupControllerRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('BackupController créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


}
