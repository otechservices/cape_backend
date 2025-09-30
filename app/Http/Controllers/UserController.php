<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

use Auth,Hash,Str;

class UserController extends Controller
{
        /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index(Request $request)

    {
          
        $users=User::with(['cps','department','roles'])->get();
        return response()->json([
            "success"=>true,
            "message"=>"liste des utilisateurs",
            "data"=>$users
        ],200);


    }
    /**

     * Store a newly created resource in storage.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function store(Request $request)

    {
        $datas=$request->all();
        unset($datas['role']);
        $datas['password']=Hash::make("123");
        $datas['code']=Str::uuid();
        $datas['name']=$request?->lastname." ".$request->firstname;
        $user=User::create($datas);
        $user->assignRole(Role::whereName($request->role)->first());

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un utilisateur",
            "data"=>$user
        ],200);

    }

    

    /**

     * Display the specified resource.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function show($id)

    {

        $user=User::find($id);

        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un utilisateur",
            "data"=>$user
        ],200);
    }
    /**

     * Update the specified resource in storage.

     *

     * @param  \Illuminate\Http\Request  $request

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function update(Request $request, User $user)

    {
       /* $request->validate([
            'username' => 'required',
            'email' => 'required'
        ]);

  */
    $datas= $request->all();
    $datas['name']=$request?->lastname." ".$request->firstname;
    $role=$request->role;
    unset($datas['role']);
   
    if ($user->roles()->first()->name != $role) {
        $user->removeRole(Role::whereName($user->roles()->first()->name)->first());
        $user->assignRole(Role::whereName($request->role)->first());
    }
    $user->update($datas);


        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un utilisateur",
            "data"=>$user
        ],200);
    }


    public function signCode(Request $request)
    {

        $datas= $request->all();
        unset($datas['sign_code_confirm']);
        $datas['sign_code']=Hash::make($datas['sign_code']);
        $user=Auth::user();
        $user->update($datas);
        return response()->json([
            "success"=>true,
            "message"=>"Création de code de signature",
            "data"=>null
        ],200);
    }

    public function changeUserStatus (Request $request)
    {
       $user = User::find($request->user_id);
       $user-> status - $request->status;
       $user->save();

       return response()->json(['success'=>'Le statut de ce utilisateur est changé avec succès']);


    }

    

    /**

     * Remove the specified resource from storage.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function destroy($id)

    {
        $user=User::find($id);
        $user->delete();
        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un utilisateur",
            "data"=>null
        ],200);
    }


    public function setStatus($id,$status)
    {
        $user=User::find($id);
        $user->update(['is_active' =>$status]);
        return response()->json([
            "success"=>true,
            "message"=>"Status mis à jour avec succès",
            "data"=>null
        ],200);
=======
use App\Http\Repositories\UserRepository;
use App\Http\Requests\User\StoreCandidateUserRequest;
use App\Http\Requests\User\StorePRUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /**
     * The user repository being queried.
     *
     * @var UserRepository
     */
    protected $userRepository;

    protected $ls;

    public function __construct(UserRepository $userRepository, LogService $ls)
    {
        $this->userRepository = $userRepository;
        $this->ls = $ls;

        $this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/users",
     *      operationId="User list",
     *      tags={"User"},
     *       security={{"JWT":{}}},
     *      summary="Return user data",
     *      description="Get all users",
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
     * @OA\Parameter(
     *          name="project_id",
     *          in="query",
     *          description="Project ID",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     * @OA\Parameter(
     *          name="role",
     *          in="query",
     *          description="User Role ID",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="categorie",
     *          in="query",
     *          description="Can be used for filtering data by categorie| ANIMATRICE,RESPONSABLE",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *          ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
        $message = 'Récupération de la liste des utilisateurs';

        try {
            $result = $this->userRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/users-notified",
     *      operationId="User notified list",
     *      tags={"User"},
     *      summary="Return user data",
     *      description="Get all users",
     *
     * @OA\Parameter(
     *          name="project_id",
     *          in="query",
     *          description="Project ID",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     * @OA\Parameter(
     *          name="role",
     *          in="query",
     *          description="User Role ID",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
    public function getNotified(Request $request)
    {

        $authorizationHeader = request()->header('Authorization');

        if ($request->header('Authorization') == null) {
            return Common::error('authorization header not found', []);
        }

        $encodedCredentials = substr($authorizationHeader, 6);

        // Décoder les identifiants
        $decodedCredentials = base64_decode($encodedCredentials);

        // Séparer l'username et le password
        [$username, $password] = explode(':', $decodedCredentials, 2);
        $envUsername = env('BASIC_AUTH_USERNAME');
        $envPassword = env('BASIC_AUTH_PASSWORD');

        if ($username !== $envUsername || $password !== $envPassword) {
            return Common::error('Accès non autorisé', []);
        }

        $message = 'Récupération de la liste des utilisateurs';

        try {
            $result = $this->userRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result->select('id'));
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/users-rh",
     *      operationId="User RH list",
     *      tags={"User"},
     *       security={{"JWT":{}}},
     *      summary="Return user data",
     *      description="Get all users",
     *
     * @OA\Parameter(
     *          name="project_id",
     *          in="query",
     *          description="Project ID",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
    public function indexRH(Request $request)
    {
        $message = 'Récupération de la liste des utilisateurs pour RH';

        try {
            $result = $this->userRepository->getAllRH($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/users/{id}",
     *      operationId="User show",
     *      tags={"User"},
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
     *          description="User ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one User data",
     *      description="Get User by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
        $message = 'Récupération d\'un utilisateur';

        try {
            $result = $this->userRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/users-rh/{id}",
     *      operationId="User RH show",
     *      tags={"User"},
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
     *          description="User ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one User data",
     *      description="Get User by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
    public function showRH($id)
    {
        $message = 'Récupération d\'un utilisateur';

        try {
            $result = $this->userRepository->getRH($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/users",
     *      operationId="User store",
     *      tags={"User"},
     *       security={{"JWT":{}}},
     *      summary="Store User data",
     *      description="Create a new User",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
    public function store(StoreUserRequest $request)
    {
        $message = 'Enregistrement d\'un utilisateur';

        try {
            $result = $this->userRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/users-candidate",
     *      operationId="Create User",
     *      tags={"User"},
     *       security={{"JWT":{}}},
     *      summary="Store User data",
     *      description="Create a new User",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserCreate")
     *
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
    public function createUser(StoreCandidateUserRequest $request)
    {
        $message = 'Enregistrement d\'un utilisateur';

        try {
            $result = $this->userRepository->makeStore2($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function createUserPR(StorePRUserRequest $request)
    {
        $message = 'Enregistrement d\'un utilisateur';

        try {
            $result = $this->userRepository->makeStorePR($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/users/{id}",
     *      operationId="User update",
     *      tags={"User"},
     *       security={{"JWT":{}}},
     *      summary="Update one User data",
     *      description="Update User by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/UserCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
    public function update(UpdateUserRequest $request, $id)
    {
        $message = 'Mise à jour d\'un utilisateur';

        try {
            $result = $this->userRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'utilisateur effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/users/{id}",
     *      operationId="User Delete",
     *      tags={"User"},
     *       security={{"JWT":{}}},
     *      summary="Delete User data",
     *      description="Delete User by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User ID",
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
        $message = 'Suppression d\'un utilisateur';

        try {
            $recup = $this->userRepository->get($id);

            $result = $this->userRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/users/{id}/state/{state}",
     *      operationId="User change state",
     *      tags={"User"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User ID",
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
     *          description="User state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change User state",
     *      description="Change User state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
        $message = 'Changement de l\'état d\'un utilisateur';

        try {
            $result = $this->userRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/users-search",
     *      operationId="User searching",
     *      tags={"User"},
     *       security={{"JWT":{}}},
     *      summary="Return list of User respecting term",
     *      description="Get all filtered users using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/User")
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
        $message = 'Filtrage des utilisateurs';

        try {
            $term = $request->term;
            $result = $this->userRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
>>>>>>> 902833d (Initial commit)
    }
}
