<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\CapeRepository;
use App\Http\Requests\Cape\StoreCapeRequest;
use App\Http\Requests\Cape\UpdateCapeRequest;
use App\Services\LogService;
use App\Utilities\Common;
use OpenApi\Attributes as OA;
use App\Utilities\ErrorMessage;


class CapeController extends Controller
{
     /**
     * The Cape repository being queried.
     *
     * @var CapeRepository
     */
    protected $CapeRepository;

    protected $ls;

    public function __construct(CapeRepository $CapeRepository, LogService $ls)
    {
        $this->CapeRepository = $CapeRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/capes",
     *      operationId="Cape list",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Return Cape data",
     *      description="Get all capes",
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
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
        $message = 'Récupération de la liste des Cape';

        try {
            $result = $this->CapeRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    /** @OA\Get(
     *      path="/capes",
     *      operationId="Cape list",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Return Cape data",
     *      description="Get all capes",
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
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
    public function getAllAuthorized(Request $request)
    {
        $message = 'Récupération de la liste des Cape ^pour CAPE';

        try {
            $result = $this->CapeRepository->getAllAuthorized($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    
    /** @OA\Get(
     *      path="/capes",
     *      operationId="Cape list",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Return Cape data",
     *      description="Get all capes",
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
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
        $message = 'Récupération de la liste des Cape ^pour CAPE';

        try {
            $result = $this->CapeRepository->getDepartmentWithRelation();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/capes/{id}",
     *      operationId="Cape show",
     *      tags={"Cape"},
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
     *          description="Cape ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one Cape data",
     *      description="Get Cape by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
        $message = 'Récupération d\'un Cape';

        try {
            $result = $this->CapeRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Cape trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    
    /** @OA\Get(
     *      path="/capes/{id}",
     *      operationId="Cape show",
     *      tags={"Cape"},
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
     *          description="Cape ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one Cape data",
     *      description="Get Cape by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
            $result = $this->CapeRepository->send($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Cape trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Post(
     *      path="/capes",
     *      operationId="Cape store",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Store Cape data",
     *      description="Create a new Cape",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CapeCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
    public function store(StoreCapeRequest $request)
    {
        $message = 'Enregistrement d\'un Cape';

        try {
            $result = $this->CapeRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Cape créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    /** @OA\Post(
     *      path="/capes",
     *      operationId="Cape store",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Store Cape data",
     *      description="Create a new Cape",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CapeCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
    public function storeDistricts(storeDistrictsCape $request)
    {
        $message = 'Enregistrement d\'un Cape';

        try {
            $result = $this->CapeRepository->storeDistricts($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Cape créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Put(
     *      path="/capes/{id}",
     *      operationId="Cape update",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Update one Cape data",
     *      description="Update Cape by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Cape ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/CapeCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
    public function update(UpdateCapeRequest $request, $id)
    {
        $message = 'Mise à jour d\'un Cape';

        try {
            $result = $this->CapeRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de Cape effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Delete(
     *      path="/capes/{id}",
     *      operationId="Cape Delete",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Delete Cape data",
     *      description="Delete Cape by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Cape ID",
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
        $message = 'Suppression de Cape';

        try {
            $recup = $this->CapeRepository->get($id);

            $result = $this->CapeRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Cape supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/capes/{id}/state/{state}",
     *      operationId="Cape change state",
     *      tags={"Cape"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Cape ID",
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
     *          description="Cape state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change Cape state",
     *      description="Change Cape state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
        $message = 'Changement de l\'état d\'un Cape';

        try {
            $result = $this->CapeRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Cape $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }

    }
    

    /** @OA\Post(
     *      path="/capes-search",
     *      operationId="Cape searching",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Return list of Cape respecting term",
     *      description="Get all filtered capes using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Cape")
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
        $message = 'Filtrage des Cape';

        try {
            $term = $request->term;
            $result = $this->CapeRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }


    
/** @OA\Post(
     *      path="/capes",
     *      operationId="Cape store",
     *      tags={"Cape"},
     *       security={{"JWT":{}}},
     *      summary="Store Cape data",
     *      description="Create a new Cape",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CapeCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Cape"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Cape")
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
        $message = 'Enregistrement d\'un Cape';

        try {
            $result = $this->CapeRepository->storeDistricts($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Cape créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }
}
