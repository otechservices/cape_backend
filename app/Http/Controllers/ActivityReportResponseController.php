<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ActivityReportResponseRepository;
use App\Http\Requests\ActivityReportResponse\StoreActivityReportResponseRequest;
use App\Http\Requests\ActivityReportResponse\UpdateActivityReportResponseRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ActivityReportResponseController extends Controller
{
    /**
     * The ActivityReportResponse repository being queried.
     *
     * @var ActivityReportResponseRepository
     */
    protected $ActivityReportResponseRepository;

    protected $ls;

    public function __construct(ActivityReportResponseRepository $ActivityReportResponseRepository, LogService $ls)
    {
        $this->ActivityReportResponseRepository = $ActivityReportResponseRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/activityReportResponses",
     *      operationId="ActivityReportResponse list",
     *      tags={"ActivityReportResponse"},
     *       security={{"JWT":{}}},
     *      summary="Return ActivityReportResponse data",
     *      description="Get all activityReportResponses",
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
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportResponse"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReportResponse")
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
        $message = 'Récupération de la liste des ActivityReportResponse';

        try {
            $result = $this->ActivityReportResponseRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/activityReportResponses/{id}",
     *      operationId="ActivityReportResponse show",
     *      tags={"ActivityReportResponse"},
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
     *          description="ActivityReportResponse ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one ActivityReportResponse data",
     *      description="Get ActivityReportResponse by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportResponse"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReportResponse")
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
        $message = 'Récupération d\'un ActivityReportResponse';

        try {
            $result = $this->ActivityReportResponseRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('ActivityReportResponse trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/activityReportResponses",
     *      operationId="ActivityReportResponse store",
     *      tags={"ActivityReportResponse"},
     *       security={{"JWT":{}}},
     *      summary="Store ActivityReportResponse data",
     *      description="Create a new ActivityReportResponse",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportResponseCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportResponse"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReportResponse")
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
    public function store(StoreActivityReportResponseRequest $request)
    {
        $message = 'Enregistrement d\'un ActivityReportResponse';

        try {
            $result = $this->ActivityReportResponseRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('ActivityReportResponse créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/activityReportResponses/{id}",
     *      operationId="ActivityReportResponse update",
     *      tags={"ActivityReportResponse"},
     *       security={{"JWT":{}}},
     *      summary="Update one ActivityReportResponse data",
     *      description="Update ActivityReportResponse by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ActivityReportResponse ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportResponseCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportResponse"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReportResponse")
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
    public function update(UpdateActivityReportResponseRequest $request, $id)
    {
        $message = 'Mise à jour d\'un ActivityReportResponse';

        try {
            $result = $this->ActivityReportResponseRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de ActivityReportResponse effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/activityReportResponses/{id}",
     *      operationId="ActivityReportResponse Delete",
     *      tags={"ActivityReportResponse"},
     *       security={{"JWT":{}}},
     *      summary="Delete ActivityReportResponse data",
     *      description="Delete ActivityReportResponse by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ActivityReportResponse ID",
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
        $message = 'Suppression de ActivityReportResponse';

        try {
            $recup = $this->ActivityReportResponseRepository->get($id);

            $result = $this->ActivityReportResponseRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('ActivityReportResponse supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/activityReportResponses/{id}/state/{state}",
     *      operationId="ActivityReportResponse change state",
     *      tags={"ActivityReportResponse"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ActivityReportResponse ID",
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
     *          description="ActivityReportResponse state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change ActivityReportResponse state",
     *      description="Change ActivityReportResponse state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportResponse"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReportResponse")
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
        $message = 'Changement de l\'état d\'un ActivityReportResponse';

        try {
            $result = $this->ActivityReportResponseRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("ActivityReportResponse $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }

    
}
