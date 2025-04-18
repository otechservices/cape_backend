<?php

namespace App\Http\Controllers;

use App\Http\Repositories\TypeacteRepository;
use App\Http\Requests\Typeacte\StoreTypeacteRequest;
use App\Http\Requests\Typeacte\UpdateTypeacteRequest;
use App\Http\Requests\Typeacte\GenerateLinkRequest;
use App\Http\Requests\Typeacte\VerifyLinkRequest;
use App\Http\Requests\Typeacte\ParticipateRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TypeacteController extends Controller
{
    /**
     * The Typeacte repository being queried.
     *
     * @var TypeacteRepository
     */
    protected $TypeacteRepository;

    protected $ls;

    public function __construct(TypeacteRepository $TypeacteRepository, LogService $ls)
    {
        $this->TypeacteRepository = $TypeacteRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/Typeactes",
     *      operationId="Typeacte list",
     *      tags={"Typeacte"},
     *       security={{"JWT":{}}},
     *      summary="Return Typeacte data",
     *      description="Get all Typeactes",
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
     *          @OA\JsonContent(ref="#/components/schemas/Typeacte"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Typeacte")
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
        $message = 'Récupération de la liste des Typeacte';

        try {
            $result = $this->TypeacteRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/Typeactes/{id}",
     *      operationId="Typeacte show",
     *      tags={"Typeacte"},
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
     *          description="Typeacte ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one Typeacte data",
     *      description="Get Typeacte by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Typeacte"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Typeacte")
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
        $message = 'Récupération d\'un Typeacte';

        try {
            $result = $this->TypeacteRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Typeacte trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/Typeactes",
     *      operationId="Typeacte store",
     *      tags={"Typeacte"},
     *       security={{"JWT":{}}},
     *      summary="Store Typeacte data",
     *      description="Create a new Typeacte",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/TypeacteCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Typeacte"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Typeacte")
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
    public function store(StoreTypeacteRequest $request)
    {
        $message = 'Enregistrement d\'un Typeacte';

        try {
            $result = $this->TypeacteRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Typeacte créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/Typeactes/{id}",
     *      operationId="Typeacte update",
     *      tags={"Typeacte"},
     *       security={{"JWT":{}}},
     *      summary="Update one Typeacte data",
     *      description="Update Typeacte by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Typeacte ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/TypeacteCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Typeacte"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Typeacte")
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
    public function update(UpdateTypeacteRequest $request, $id)
    {
        $message = 'Mise à jour d\'un Typeacte';

        try {
            $result = $this->TypeacteRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de Typeacte effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/Typeactes/{id}",
     *      operationId="Typeacte Delete",
     *      tags={"Typeacte"},
     *       security={{"JWT":{}}},
     *      summary="Delete Typeacte data",
     *      description="Delete Typeacte by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Typeacte ID",
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
        $message = 'Suppression de Typeacte';

        try {
            $recup = $this->TypeacteRepository->get($id);

            $result = $this->TypeacteRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Typeacte supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/Typeactes/{id}/state/{state}",
     *      operationId="Typeacte change state",
     *      tags={"Typeacte"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Typeacte ID",
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
     *          description="Typeacte state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change Typeacte state",
     *      description="Change Typeacte state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Typeacte"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Typeacte")
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
        $message = 'Changement de l\'état d\'un Typeacte';

        try {
            $result = $this->TypeacteRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Typeacte $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/Typeactes-search",
     *      operationId="Typeacte searching",
     *      tags={"Typeacte"},
     *       security={{"JWT":{}}},
     *      summary="Return list of Typeacte respecting term",
     *      description="Get all filtered Typeactes using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Typeacte"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Typeacte")
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
        $message = 'Filtrage des Typeacte';

        try {
            $term = $request->term;
            $result = $this->TypeacteRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    function generateLink(GenerateLinkRequest $request,$id) {
        $message = 'Génération de lien de Typeacte';

        try {
            $result = $this->TypeacteRepository->generateLink($id,$request->validated());
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
            $result = $this->TypeacteRepository->verifyLink ($request->validated());
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
            
            $result = $this->TypeacteRepository->participate ($request->validated());
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

            if (!$this->TypeacteRepository->get($id)) {
                return Common::error('Aucun Typeacte n\'existe à cette référence.', []);

            }
            
            $result = $this->TypeacteRepository->generateMediaLink($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($id)]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
