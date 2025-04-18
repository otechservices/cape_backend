<?php

namespace App\Http\Controllers;

use App\Http\Repositories\NotationAgentRepository;
use App\Http\Requests\NotationAgent\StoreNotationAgentRequest;
use App\Http\Requests\NotationAgent\UpdateNotationAgentRequest;
use App\Http\Requests\NotationAgent\GenerateLinkRequest;
use App\Http\Requests\NotationAgent\VerifyLinkRequest;
use App\Http\Requests\NotationAgent\ParticipateRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotationAgentController extends Controller
{
    /**
     * The NotationAgent repository being queried.
     *
     * @var NotationAgentRepository
     */
    protected $NotationAgentRepository;

    protected $ls;

    public function __construct(NotationAgentRepository $NotationAgentRepository, LogService $ls)
    {
        $this->NotationAgentRepository = $NotationAgentRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/NotationAgents",
     *      operationId="NotationAgent list",
     *      tags={"NotationAgent"},
     *       security={{"JWT":{}}},
     *      summary="Return NotationAgent data",
     *      description="Get all NotationAgents",
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
     *          @OA\JsonContent(ref="#/components/schemas/NotationAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/NotationAgent")
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
        $message = 'Récupération de la liste des NotationAgent';

        try {
            $result = $this->NotationAgentRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/NotationAgents/{id}",
     *      operationId="NotationAgent show",
     *      tags={"NotationAgent"},
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
     *          description="NotationAgent ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one NotationAgent data",
     *      description="Get NotationAgent by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/NotationAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/NotationAgent")
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
        $message = 'Récupération d\'un NotationAgent';

        try {
            $result = $this->NotationAgentRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('NotationAgent trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/NotationAgents",
     *      operationId="NotationAgent store",
     *      tags={"NotationAgent"},
     *       security={{"JWT":{}}},
     *      summary="Store NotationAgent data",
     *      description="Create a new NotationAgent",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/NotationAgentCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/NotationAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/NotationAgent")
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
    public function store(StoreNotationAgentRequest $request)
    {
        $message = 'Enregistrement d\'un NotationAgent';

        try {
            $result = $this->NotationAgentRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('NotationAgent créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/NotationAgents/{id}",
     *      operationId="NotationAgent update",
     *      tags={"NotationAgent"},
     *       security={{"JWT":{}}},
     *      summary="Update one NotationAgent data",
     *      description="Update NotationAgent by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="NotationAgent ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/NotationAgentCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/NotationAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/NotationAgent")
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
    public function update(UpdateNotationAgentRequest $request, $id)
    {
        $message = 'Mise à jour d\'un NotationAgent';

        try {
            $result = $this->NotationAgentRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de NotationAgent effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/NotationAgents/{id}",
     *      operationId="NotationAgent Delete",
     *      tags={"NotationAgent"},
     *       security={{"JWT":{}}},
     *      summary="Delete NotationAgent data",
     *      description="Delete NotationAgent by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="NotationAgent ID",
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
        $message = 'Suppression de NotationAgent';

        try {
            $recup = $this->NotationAgentRepository->get($id);

            $result = $this->NotationAgentRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('NotationAgent supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/NotationAgents/{id}/state/{state}",
     *      operationId="NotationAgent change state",
     *      tags={"NotationAgent"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="NotationAgent ID",
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
     *          description="NotationAgent state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change NotationAgent state",
     *      description="Change NotationAgent state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/NotationAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/NotationAgent")
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
        $message = 'Changement de l\'état d\'un NotationAgent';

        try {
            $result = $this->NotationAgentRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("NotationAgent $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/NotationAgents-search",
     *      operationId="NotationAgent searching",
     *      tags={"NotationAgent"},
     *       security={{"JWT":{}}},
     *      summary="Return list of NotationAgent respecting term",
     *      description="Get all filtered NotationAgents using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/NotationAgent"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/NotationAgent")
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
        $message = 'Filtrage des NotationAgent';

        try {
            $term = $request->term;
            $result = $this->NotationAgentRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    function generateLink(GenerateLinkRequest $request,$id) {
        $message = 'Génération de lien de NotationAgent';

        try {
            $result = $this->NotationAgentRepository->generateLink($id,$request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
    function verifyLink(VerifyLinkRequest $request) {
        $message = 'Récupération de fêtes';

        try {
            $result = $this->NotationAgentRepository->verifyLink ($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
    function participate(ParticipateRequest $request) {
        $message = 'Participation de fêtes';

        try {
            
            $result = $this->NotationAgentRepository->participate ($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
    function generateMediaLink($id) {
        $message = 'Génération de lien média';

        try {

            if (!$this->NotationAgentRepository->get($id)) {
                return Common::error('Aucun NotationAgent n\'existe à cette référence.', []);

            }
            
            $result = $this->NotationAgentRepository->generateMediaLink($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($id)]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
