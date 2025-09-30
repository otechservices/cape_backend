<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Exercise;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
           // $token->expires_at = Carbon::now()->addWeeks(1);
          
           $tokenResult->token->expires_at = Carbon::now()->addHour(1);
           $tokenResult->token->save();
        activity()->log('Utilisateur connecté le '.date('d-m-Y h:i:s'));
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        if (Auth::user()->roles()->first()->name == "cps") {
            $user=User::with("cps")->whereId(Auth::id())->first();
        }else {
            $user=User::whereId(Auth::id())->first();
        }

        $user->roles;
        return response()->json($user);
    }

    public function changePassword(Request $request){
        $user=User::find(Auth::id());
        if ($request->confirm_password != $request->new_password  ) {
            return response()->json([
                "success"=>true,
                "message"=>"Mots de passe non identique",
                "data"=>[]
            ],500);
        }elseif (!Hash::check($request->old_password,$user->password)) {
            return response()->json([
                "success"=>true,
                "message"=>"Ancien mot de passe incorrect",
                "data"=>[]
            ],500);
        }else{
            $user->update(['password'=>Hash::make($request->new_password)]);
            return response()->json([
                "success"=>true,
                "message"=>"Mot de passe changé avec succès",
                "data"=>[]
            ],200);

        }
        
    }
    public function changeFirstPassword(Request $request){
        $user=User::find(Auth::id());
        if ($request->confirm_password != $request->password  ) {
            return response()->json([
                "success"=>true,
                "message"=>"Mots de passe non identique",
                "data"=>[]
            ],500);
        }else{
           
            $user->update([
                'password'=>Hash::make($request->password),
                'first_signin'=>0
            ]);
            return response()->json([
                "success"=>true,
                "message"=>"Mot de passe changé avec succès",
                "data"=>['ici']
            ],200);

        }
        
    }

    public function loggedUserData(){
        try {
            $userName = auth()->user()->username;
            $userEmail = auth()->user()->email;
            return response([
                'status'=>'success',
                'message' => 'Données utilisateur enregistrées',
                'user data'=>[
                    'username' =>$userName,
                    'email' =>$userEmail,
                ]
             
            ], 200);
           
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
           }
    }

    public function update(Request $request)
    {
       
        $datas=$request->all();

        $user=User::find(Auth::id());

        $user->update($datas);

        $user=User::find(Auth::id());

        return response()->json([
            "success"=>true,
            "message"=>"Modification d'un utilisateur",
            "data"=>$user
        ],200);
    
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Vous êtes déconnecté avec succès'
        ]);
    }

    
}