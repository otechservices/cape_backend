<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use DB;

class JournalController extends Controller
{
    public function index()
    {
        $activity_logs=DB::table("activity_log")
        ->join('users', 'activity_log.causer_id', '=', 'users.id')
        ->select('activity_log.*', 'users.name')
        ->get();
        return response()->json([
            "success"=>true,
            "message"=>"Historique des activités",
            "data"=>$activity_logs
        ],200);
    }

}
