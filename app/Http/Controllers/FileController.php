<?php

namespace App\Http\Controllers;

use App\Utilities\ErrorMessage;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Service;
use App\Models\RequeteFile;
use App\Http\Repositories\FileRepository;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\File\UpdateFileRequest;
use App\Services\LogService;
use App\Utilities\Common;
use OpenApi\Attributes as OA;

use Auth;


class FileController extends Controller
{
 /**
     * The File repository being queried.
     *
     * @var FileRepository
     */
    protected $fileRepository;

    protected $ls;

    public function __construct(FileRepository $fileRepository, LogService $ls)
    {
        $this->fileRepository = $fileRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/agents",
     *      operationId="File list",
     *      tags={"File"},
     *       security={{"JWT":{}}},
     *      summary="Return File data",
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
     *          @OA\JsonContent(ref="#/components/schemas/File"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/File")
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
        $message = 'Récupération de la liste des File';

        try {
            $result = $this->fileRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/agents/{id}",
     *      operationId="File show",
     *      tags={"File"},
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
     *          description="File ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one File data",
     *      description="Get File by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/File"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/File")
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
        $message = 'Récupération d\'un File';

        try {
            $result = $this->fileRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('File trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Post(
     *      path="/agents",
     *      operationId="File store",
     *      tags={"File"},
     *       security={{"JWT":{}}},
     *      summary="Store File data",
     *      description="Create a new File",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/FileCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/File"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/File")
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
    public function store(StoreFileRequest $request)
    {
        $message = 'Enregistrement d\'un File';

        try {
            $result = $this->fileRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('File créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Put(
     *      path="/agents/{id}",
     *      operationId="File update",
     *      tags={"File"},
     *       security={{"JWT":{}}},
     *      summary="Update one File data",
     *      description="Update File by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="File ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/FileCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/File"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/File")
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
    public function update(UpdateFileRequest $request, $id)
    {
        $message = 'Mise à jour d\'un File';

        try {
            $result = $this->fileRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de File effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Delete(
     *      path="/agents/{id}",
     *      operationId="File Delete",
     *      tags={"File"},
     *       security={{"JWT":{}}},
     *      summary="Delete File data",
     *      description="Delete File by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="File ID",
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
        $message = 'Suppression de File';

        try {
            $recup = $this->fileRepository->get($id);

            $result = $this->fileRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('File supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** @OA\Get(
     *      path="/agents/{id}/state/{state}",
     *      operationId="File change state",
     *      tags={"File"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="File ID",
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
     *          description="File state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change File state",
     *      description="Change File state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/File"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/File")
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
        $message = 'Changement de l\'état d\'un File';

        try {
            $result = $this->fileRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("File $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }

    }

    /** @OA\Post(
     *      path="/agents-search",
     *      operationId="File searching",
     *      tags={"File"},
     *       security={{"JWT":{}}},
     *      summary="Return list of File respecting term",
     *      description="Get all filtered agents using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/File"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/File")
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
        $message = 'Filtrage des File';

        try {
            $term = $request->term;
            $result = $this->fileRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => ErrorMessage::report($th)]);

            return Common::error(ErrorMessage::of($th), []);
        }
    }
}
