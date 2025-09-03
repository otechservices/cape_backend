<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

     /*   if(Auth::user()->hasRole('Admin')){
            $roles=Role::with("permissions")->get();
            $permissions=Permission::all();
        return response()->json(compact("roles","permissions"),200);


        }elseif (Auth::user()->hasRole('Admin Sectoriel')) {
            $roles=Role::where("name","!=","Admin")->get();
            $permissions=Permission::all();
        return response()->json(compact("roles","permissions"),200);

        }*/

        $roles=Role::with("permissions")->get();
        $permissions=Permission::all();
             return response()->json(compact("roles","permissions"),200);

        }
    
           
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
     public function store(Request $request)
    {
        $role=Role::find($request->id);
        $role->givePermissionTo(Permission::where('id',$request->permission_id)->first());
        return response()->json([],200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role=Role::find($id);
        $role->revokePermissionTo(Permission::where('id',$request->id)->first());
        return response()->json([],200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
