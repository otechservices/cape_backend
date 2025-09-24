<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $permissions = Permission::all();

        return response()->json([
            "success"=>true,
            "message"=>"Liste des permissions",
            "data"=>$permissions
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
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create($request->only('name'));

        return response()->json([
            "success"=>true,
            "message"=>"Enregistrement d'une priorité",
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permissions)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$permissions->id
        ]);

        $permissions->update($request->only('name'));

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'une permission",
            "data"=>$permissions
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            "success"=>true,
            "message"=>"Suppression d'une permission",
            "data"=>null
        ],200);
    }
}
