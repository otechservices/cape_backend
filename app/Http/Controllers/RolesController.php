<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Auth;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {

    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   

        if(Auth::user()->hasRole('Admin')){
            $roles=Role::all();
        }else {
            $roles=Role::whereNotIn('name',['Admin','Admin Sectoriel'])->get();
        
        
    }
        
      
        return response()->json([
            "success"=>true,
            "message"=>"Liste des roles",
            "data"=>$roles
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
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
        ]);
        $role = Role::create(['name' => $request->get('name')]);
    
        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'un role",
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $role = $role;
        return response()->json([
            "success"=>true,
            "message"=>"Récupération d'un rôle",
            "data"=>$role
        ],200);
    }   
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Role $role, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        
        $role->update($request->only('name'));
    
        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un role",
            "data"=>$role
        ],200);
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'un role",
            "data"=>null
        ],200);
    }
}
