<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\ActivityReportRepository;
use App\Http\Requests\ActivityReport\StoreActivityReportRequest;
use App\Http\Requests\ActivityReport\UpdateActivityReportRequest;
use App\Services\LogService;
use App\Utilities\Common;
use OpenApi\Attributes as OA;


class ActivityReportController extends Controller
{
     /**
     * The ActivityReport repository being queried.
     *
     * @var ActivityReportRepository
     */
    protected $ActivityReportRepository;

    protected $ls;

    public function __construct(ActivityReportRepository $ActivityReportRepository, LogService $ls)
    {
        $this->ActivityReportRepository = $ActivityReportRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/activityReports",
     *      operationId="ActivityReport list",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Return ActivityReport data",
     *      description="Get all activityReports",
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
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
        $message = 'Récupération de la liste des ActivityReport';

        try {
            $result = $this->ActivityReportRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/activityReports",
     *      operationId="ActivityReport list",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Return ActivityReport data",
     *      description="Get all activityReports",
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
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
    public function getForCape(Request $request)
    {
        $message = 'Récupération de la liste des ActivityReport ^pour CAPE';

        try {
            $result = $this->ActivityReportRepository->getForCape($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    
    /** @OA\Get(
     *      path="/activityReports",
     *      operationId="ActivityReport list",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Return ActivityReport data",
     *      description="Get all activityReports",
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
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
        $message = 'Récupération de la liste des ActivityReport ^pour CAPE';

        try {
            $result = $this->ActivityReportRepository->getDepartmentWithRelation();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/activityReports/{id}",
     *      operationId="ActivityReport show",
     *      tags={"ActivityReport"},
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
     *          description="ActivityReport ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one ActivityReport data",
     *      description="Get ActivityReport by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
        $message = 'Récupération d\'un ActivityReport';

        try {
            $result = $this->ActivityReportRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('ActivityReport trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    
    /** @OA\Get(
     *      path="/activityReports/{id}",
     *      operationId="ActivityReport show",
     *      tags={"ActivityReport"},
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
     *          description="ActivityReport ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one ActivityReport data",
     *      description="Get ActivityReport by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
    public function send(Request $request, $id)
    {
        $message = 'Envoi e rapport';

        try {
            $result = $this->ActivityReportRepository->send($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('ActivityReport trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/activityReports",
     *      operationId="ActivityReport store",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Store ActivityReport data",
     *      description="Create a new ActivityReport",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
    public function store(StoreActivityReportRequest $request)
    {
        $message = 'Enregistrement d\'un ActivityReport';

        try {
            $result = $this->ActivityReportRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('ActivityReport créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/activityReports",
     *      operationId="ActivityReport store",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Store ActivityReport data",
     *      description="Create a new ActivityReport",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
    public function storeDistricts(storeDistrictsActivityReport $request)
    {
        $message = 'Enregistrement d\'un ActivityReport';

        try {
            $result = $this->ActivityReportRepository->storeDistricts($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('ActivityReport créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/activityReports/{id}",
     *      operationId="ActivityReport update",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Update one ActivityReport data",
     *      description="Update ActivityReport by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ActivityReport ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
    public function update(UpdateActivityReportRequest $request, $id)
    {
        $message = 'Mise à jour d\'un ActivityReport';

        try {
            $result = $this->ActivityReportRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de ActivityReport effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/activityReports/{id}",
     *      operationId="ActivityReport Delete",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Delete ActivityReport data",
     *      description="Delete ActivityReport by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ActivityReport ID",
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
        $message = 'Suppression de ActivityReport';

        try {
            $recup = $this->ActivityReportRepository->get($id);

            $result = $this->ActivityReportRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('ActivityReport supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/activityReports/{id}/state/{state}",
     *      operationId="ActivityReport change state",
     *      tags={"ActivityReport"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ActivityReport ID",
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
     *          description="ActivityReport state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change ActivityReport state",
     *      description="Change ActivityReport state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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
        $message = 'Changement de l\'état d\'un ActivityReport';

        try {
            $result = $this->ActivityReportRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("ActivityReport $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }
    

    /** @OA\Post(
     *      path="/activityReports-search",
     *      operationId="ActivityReport searching",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Return list of ActivityReport respecting term",
     *      description="Get all filtered activityReports using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/ActivityReport")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/TermSearch")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function search(Request $request)
    {
        $message = 'Filtrage des ActivityReport';

        try {
            $term = $request->term;
            $result = $this->ActivityReportRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    
/** @OA\Post(
     *      path="/activityReports",
     *      operationId="ActivityReport store",
     *      tags={"ActivityReport"},
     *       security={{"JWT":{}}},
     *      summary="Store ActivityReport data",
     *      description="Create a new ActivityReport",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReportCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ActivityReport"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/ActivityReport")
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


public function exportPDF()
    {
        $message = 'Enregistrement d\'un ActivityReport';

        try {
            $result = $this->ActivityReportRepository->storeDistricts($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('ActivityReport créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
