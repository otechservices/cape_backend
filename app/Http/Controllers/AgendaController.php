<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\AgendaControllerRepository;
use App\Http\Requests\AgendaController\StoreAgendaControllerRequest;
use App\Http\Requests\AgendaController\UpdateAgendaControllerRequest;
use App\Services\LogService;
use App\Utilities\Common;
use OpenApi\Attributes as OA;
use App\Utilities\ErrorMessage;


class AgendaControllerController extends Controller
{
     /**
     * The AgendaController repository being queried.
     *
     * @var AgendaControllerRepository
     */
    protected $AgendaControllerRepository;

    protected $ls;

    public function __construct(AgendaControllerRepository $AgendaControllerRepository, LogService $ls)
    {
        $this->AgendaControllerRepository = $AgendaControllerRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/agendas",
     *      operationId="AgendaController list",
     *      tags={"AgendaController"},
     *       security={{"JWT":{}}},
     *      summary="Return AgendaController data",
     *      description="Get all agendas",
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
     *          @OA\JsonContent(ref="#/components/schemas/AgendaController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AgendaController")
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
        $message = 'Récupération de la liste des AgendaController';

        try {
            $result = $this->AgendaControllerRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    
    /** @OA\Get(
     *      path="/agendas",
     *      operationId="AgendaController list",
     *      tags={"AgendaController"},
     *       security={{"JWT":{}}},
     *      summary="Return AgendaController data",
     *      description="Get all agendas",
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
     *          @OA\JsonContent(ref="#/components/schemas/AgendaController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AgendaController")
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
        $message = 'Récupération de la liste des AgendaController ^pour CAPE';

        try {
            $result = $this->AgendaControllerRepository->getDepartmentWithRelation();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/agendas/{id}",
     *      operationId="AgendaController show",
     *      tags={"AgendaController"},
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
     *          description="AgendaController ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AgendaController data",
     *      description="Get AgendaController by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AgendaController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AgendaController")
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
        $message = 'Récupération d\'un AgendaController';

        try {
            $result = $this->AgendaControllerRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('AgendaController trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    /** @OA\Post(
     *      path="/agendas",
     *      operationId="AgendaController store",
     *      tags={"AgendaController"},
     *       security={{"JWT":{}}},
     *      summary="Store AgendaController data",
     *      description="Create a new AgendaController",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AgendaControllerCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AgendaController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AgendaController")
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
    public function store(StoreAgendaControllerRequest $request)
    {
        $message = 'Enregistrement d\'un AgendaController';

        try {
            $result = $this->AgendaControllerRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('AgendaController créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    /** @OA\Put(
     *      path="/agendas/{id}",
     *      operationId="AgendaController update",
     *      tags={"AgendaController"},
     *       security={{"JWT":{}}},
     *      summary="Update one AgendaController data",
     *      description="Update AgendaController by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AgendaController ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AgendaControllerCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AgendaController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AgendaController")
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
    public function update(UpdateAgendaControllerRequest $request, $id)
    {
        $message = 'Mise à jour d\'un AgendaController';

        try {
            $result = $this->AgendaControllerRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de AgendaController effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Delete(
     *      path="/agendas/{id}",
     *      operationId="AgendaController Delete",
     *      tags={"AgendaController"},
     *       security={{"JWT":{}}},
     *      summary="Delete AgendaController data",
     *      description="Delete AgendaController by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AgendaController ID",
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
        $message = 'Suppression de AgendaController';

        try {
            $recup = $this->AgendaControllerRepository->get($id);

            $result = $this->AgendaControllerRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('AgendaController supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/agendas/{id}/state/{state}",
     *      operationId="AgendaController change state",
     *      tags={"AgendaController"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AgendaController ID",
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
     *          description="AgendaController state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change AgendaController state",
     *      description="Change AgendaController state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AgendaController"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AgendaController")
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
        $message = 'Changement de l\'état d\'un AgendaController';

        try {
            $result = $this->AgendaControllerRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("AgendaController $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }

    }

}
