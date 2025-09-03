<?php

namespace App\Http\Controllers;

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
    }
}
