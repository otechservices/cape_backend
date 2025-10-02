<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

class ResetPasswordController extends Controller
{
public function sendResetPasswordLink(Request $request){
    try {
    $request->validate([
        'email' => 'required|email',
    ]);
      $reqEmail = $request->email;
    $user = User::where('email', $reqEmail)->first();
    if(!$user){
        return response([
            'status'=>'failed',
            'message'=>'Cet email est introuvable'
        ], 500);
    }
    $token = Str::random(64);
    PasswordReset::create([
        'email'=>$reqEmail,
        'token'=>$token,
        'created_at'=>Carbon::now()
    ]);
    $userName = $user->name;
    Mail::send('emails.resetPassword', ['token'=>$token,'userName'=>$userName], function(Message $message)use($reqEmail){
        $message->subject('Réinitialiser votre mot de passe');
        $message->to($reqEmail);
    });
    return response([
        'status'=>'success',
        'message'=>'Vérifiez votre email...... Réinitialisez votre mot de passe',
    ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
   
}

public function recoveryPassword(Request $request, $token){

    $checkToken= PasswordReset::where('token',$token)->first();
  
    if ($checkToken) {
    if ($request->confirm_password != $request->password  ) {
        return response()->json([
            "success"=>true,
            "message"=>"Mots de passe non identique",
            "data"=>[]
        ],500);
    }else{
        $user=User::whereEmail($checkToken->email);

        $user->update(['password'=>Hash::make($request->password)]);
        $q = 'DELETE FROM password_resets where token = ?';
        DB::delete($q, [$token]);
        
        return response()->json([
            "success"=>true,
            "message"=>"Mot de passe changé avec succès",
            "data"=>[]
        ],200);

    }
    } else {
        return response()->json([
            "success"=>true,
            "message"=>"Cet token n'existe pas ",
            "data"=>[]
        ],500);
    }
    
   
}
}