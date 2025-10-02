<?php

namespace App\Http\Controllers;

use App\Http\Repositories\JoursFeriesRepository;
use App\Http\Requests\JoursFeries\StoreJoursFeriesRequest;
use App\Http\Requests\JoursFeries\UpdateJoursFeriesRequest;
use App\Http\Requests\JoursFeries\GenerateLinkRequest;
use App\Http\Requests\JoursFeries\VerifyLinkRequest;
use App\Http\Requests\JoursFeries\ParticipateRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class JoursFeriesController extends Controller
{
    /**
     * The JoursFeries repository being queried.
     *
     * @var JoursFeriesRepository
     */
    protected $JoursFeriesRepository;

    protected $ls;

    public function __construct(JoursFeriesRepository $JoursFeriesRepository, LogService $ls)
    {
        $this->JoursFeriesRepository = $JoursFeriesRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/JoursFeriess",
     *      operationId="JoursFeries list",
     *      tags={"JoursFeries"},
     *       security={{"JWT":{}}},
     *      summary="Return JoursFeries data",
     *      description="Get all JoursFeriess",
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
     *          @OA\JsonContent(ref="#/components/schemas/JoursFeries"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/JoursFeries")
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
        $message = 'Récupération de la liste des JoursFeries';

        try {
            $result = $this->JoursFeriesRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/JoursFeriess/{id}",
     *      operationId="JoursFeries show",
     *      tags={"JoursFeries"},
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
     *          description="JoursFeries ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one JoursFeries data",
     *      description="Get JoursFeries by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/JoursFeries"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/JoursFeries")
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
        $message = 'Récupération d\'un JoursFeries';

        try {
            $result = $this->JoursFeriesRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('JoursFeries trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/JoursFeriess",
     *      operationId="JoursFeries store",
     *      tags={"JoursFeries"},
     *       security={{"JWT":{}}},
     *      summary="Store JoursFeries data",
     *      description="Create a new JoursFeries",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/JoursFeriesCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/JoursFeries"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/JoursFeries")
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
    public function store(StoreJoursFeriesRequest $request)
    {
        $message = 'Enregistrement d\'un JoursFeries';

        try {
            $result = $this->JoursFeriesRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('JoursFeries créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/JoursFeriess/{id}",
     *      operationId="JoursFeries update",
     *      tags={"JoursFeries"},
     *       security={{"JWT":{}}},
     *      summary="Update one JoursFeries data",
     *      description="Update JoursFeries by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="JoursFeries ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/JoursFeriesCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/JoursFeries"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/JoursFeries")
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
    public function update(UpdateJoursFeriesRequest $request, $id)
    {
        $message = 'Mise à jour d\'un JoursFeries';

        try {
            $result = $this->JoursFeriesRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de JoursFeries effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/JoursFeriess/{id}",
     *      operationId="JoursFeries Delete",
     *      tags={"JoursFeries"},
     *       security={{"JWT":{}}},
     *      summary="Delete JoursFeries data",
     *      description="Delete JoursFeries by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="JoursFeries ID",
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
        $message = 'Suppression de JoursFeries';

        try {
            $recup = $this->JoursFeriesRepository->get($id);

            $result = $this->JoursFeriesRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('JoursFeries supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/JoursFeriess/{id}/state/{state}",
     *      operationId="JoursFeries change state",
     *      tags={"JoursFeries"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="JoursFeries ID",
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
     *          description="JoursFeries state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change JoursFeries state",
     *      description="Change JoursFeries state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/JoursFeries"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/JoursFeries")
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
        $message = 'Changement de l\'état d\'un JoursFeries';

        try {
            $result = $this->JoursFeriesRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("JoursFeries $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/JoursFeriess-search",
     *      operationId="JoursFeries searching",
     *      tags={"JoursFeries"},
     *       security={{"JWT":{}}},
     *      summary="Return list of JoursFeries respecting term",
     *      description="Get all filtered JoursFeriess using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/JoursFeries"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/JoursFeries")
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
        $message = 'Filtrage des JoursFeries';

        try {
            $term = $request->term;
            $result = $this->JoursFeriesRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    function generateLink(GenerateLinkRequest $request,$id) {
        $message = 'Génération de lien de JoursFeries';

        try {
            $result = $this->JoursFeriesRepository->generateLink($id,$request->validated());
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
            $result = $this->JoursFeriesRepository->verifyLink ($request->validated());
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
            
            $result = $this->JoursFeriesRepository->participate ($request->validated());
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

            if (!$this->JoursFeriesRepository->get($id)) {
                return Common::error('Aucun JoursFeries n\'existe à cette référence.', []);

            }
            
            $result = $this->JoursFeriesRepository->generateMediaLink($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($id)]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
