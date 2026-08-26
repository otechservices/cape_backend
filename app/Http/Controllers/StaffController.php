<?php

namespace App\Http\Controllers;

use App\Utilities\ErrorMessage;

use Illuminate\Http\Request;
use App\Models\Staff;
use App\Http\Repositories\StaffRepository;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Services\LogService;
use App\Utilities\Common;
use OpenApi\Attributes as OA;
use Auth;



class StaffController extends Controller
{
    
 /**
     * The Staff repository being queried.
     *
     * @var StaffRepository
     */
    protected $staffRepository;

    protected $ls;

    public function __construct(StaffRepository $staffRepository, LogService $ls)
    {
        $this->staffRepository = $staffRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/agents",
     *      operationId="Staff list",
     *      tags={"Staff"},
     *       security={{"JWT":{}}},
     *      summary="Return Staff data",
     *      description="Get all agents",
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
     *          @OA\JsonContent(ref="#/components/schemas/Staff"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Staff")
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
        $message = 'Récupération de la liste des Staff';

        try {
            $result = $this->staffRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/agents/{id}",
     *      operationId="Staff show",
     *      tags={"Staff"},
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
     *          description="Staff ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one Staff data",
     *      description="Get Staff by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Staff"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Staff")
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
        $message = 'Récupération d\'un Staff';

        try {
            $result = $this->staffRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Staff trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Post(
     *      path="/agents",
     *      operationId="Staff store",
     *      tags={"Staff"},
     *       security={{"JWT":{}}},
     *      summary="Store Staff data",
     *      description="Create a new Staff",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/StaffCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Staff"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Staff")
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
    public function store(StoreStaffRequest $request)
    {
        $message = 'Enregistrement d\'un Staff';

        try {
            $result = $this->staffRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Staff créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Put(
     *      path="/agents/{id}",
     *      operationId="Staff update",
     *      tags={"Staff"},
     *       security={{"JWT":{}}},
     *      summary="Update one Staff data",
     *      description="Update Staff by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Staff ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/StaffCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Staff"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Staff")
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
    public function update(UpdateStaffRequest $request, $id)
    {
        $message = 'Mise à jour d\'un Staff';

        try {
            $result = $this->staffRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de Staff effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Delete(
     *      path="/agents/{id}",
     *      operationId="Staff Delete",
     *      tags={"Staff"},
     *       security={{"JWT":{}}},
     *      summary="Delete Staff data",
     *      description="Delete Staff by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Staff ID",
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
        $message = 'Suppression de Staff';

        try {
            $recup = $this->staffRepository->get($id);

            $result = $this->staffRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Staff supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/agents/{id}/state/{state}",
     *      operationId="Staff change state",
     *      tags={"Staff"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Staff ID",
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
     *          description="Staff state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change Staff state",
     *      description="Change Staff state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Staff"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Staff")
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
    public function changeState($id, $state)
    {
        $message = 'Changement de l\'état d\'un Staff';

        try {
            $result = $this->staffRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Staff $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }

    }

    /** @OA\Post(
     *      path="/agents-search",
     *      operationId="Staff searching",
     *      tags={"Staff"},
     *       security={{"JWT":{}}},
     *      summary="Return list of Staff respecting term",
     *      description="Get all filtered agents using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Staff"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Staff")
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
        $message = 'Filtrage des Staff';

        try {
            $term = $request->term;
            $result = $this->staffRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


}
