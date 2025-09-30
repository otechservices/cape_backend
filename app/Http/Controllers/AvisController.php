<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\AvisControllerRepository;
use App\Http\Requests\AvisController\StoreAvisControllerRequest;
use App\Http\Requests\AvisController\UpdateAvisControllerRequest;
use App\Services\LogService;
use App\Utilities\Common;
use OpenApi\Attributes as OA;


class AvisControllerController extends Controller
{
     /**
     * The AvisController repository being queried.
     *
     * @var AvisControllerRepository
     */
    protected $AvisControllerRepository;

    protected $ls;

    public function __construct(AvisControllerRepository $AvisControllerRepository, LogService $ls)
    {
        $this->AvisControllerRepository = $AvisControllerRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/aviss",
     *      operationId="AvisController list",
     *      tags={"AvisController"},
     *       security={{"JWT":{}}},
     *      summary="Return AvisController data",
     *      description="Get all aviss",
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
     *          @OA\JsonContent(ref="#/components/schemas/AvisController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AvisController")
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
        $message = 'Récupération de la liste des AvisController';

        try {
            $result = $this->AvisControllerRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    
    /** @OA\Get(
     *      path="/aviss",
     *      operationId="AvisController list",
     *      tags={"AvisController"},
     *       security={{"JWT":{}}},
     *      summary="Return AvisController data",
     *      description="Get all aviss",
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
     *          @OA\JsonContent(ref="#/components/schemas/AvisController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AvisController")
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
    public function getDepartmentWithRelation()
    {
        $message = 'Récupération de la liste des AvisController ^pour CAPE';

        try {
            $result = $this->AvisControllerRepository->getDepartmentWithRelation();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/aviss/{id}",
     *      operationId="AvisController show",
     *      tags={"AvisController"},
     *       security={{"JWT":{}}},
     *
     *  @OA\Parameter(
     *          name="project_id",
     *          in="query",
     *          description="Project ID",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AvisController ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AvisController data",
     *      description="Get AvisController by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AvisController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AvisController")
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
    public function show(Request $request, $id)
    {
        $message = 'Récupération d\'un AvisController';

        try {
            $result = $this->AvisControllerRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('AvisController trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/aviss",
     *      operationId="AvisController store",
     *      tags={"AvisController"},
     *       security={{"JWT":{}}},
     *      summary="Store AvisController data",
     *      description="Create a new AvisController",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AvisControllerCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AvisController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AvisController")
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
    public function store(StoreAvisControllerRequest $request)
    {
        $message = 'Enregistrement d\'un AvisController';

        try {
            $result = $this->AvisControllerRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('AvisController créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Put(
     *      path="/aviss/{id}",
     *      operationId="AvisController update",
     *      tags={"AvisController"},
     *       security={{"JWT":{}}},
     *      summary="Update one AvisController data",
     *      description="Update AvisController by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AvisController ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AvisControllerCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AvisController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AvisController")
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
    public function update(UpdateAvisControllerRequest $request, $id)
    {
        $message = 'Mise à jour d\'un AvisController';

        try {
            $result = $this->AvisControllerRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de AvisController effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/aviss/{id}",
     *      operationId="AvisController Delete",
     *      tags={"AvisController"},
     *       security={{"JWT":{}}},
     *      summary="Delete AvisController data",
     *      description="Delete AvisController by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AvisController ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DeleteResponseData"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/DeleteResponseData")
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
    public function destroy($id)
    {
        $message = 'Suppression de AvisController';

        try {
            $recup = $this->AvisControllerRepository->get($id);

            $result = $this->AvisControllerRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('AvisController supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/aviss/{id}/state/{state}",
     *      operationId="AvisController change state",
     *      tags={"AvisController"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AvisController ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="state",
     *          in="path",
     *          description="AvisController state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change AvisController state",
     *      description="Change AvisController state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AvisController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AvisController")
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
    public function setStatus($id, $status)
    {
        $message = 'Changement de l\'état d\'un AvisController';

        try {
            $result = $this->AvisControllerRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("AvisController $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }

}
