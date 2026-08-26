<?php

namespace App\Http\Repositories;

use App\Exceptions\JsonResponseException;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\Promoter;
use App\Notifications\DefaultNotification;
use App\Notifications\ElectionPrClosedNotification;
use App\Services\OTPService;
use App\Traits\Repository;
use App\Utilities\Common;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use JWTAuth;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Twilio\TwilioChannel;
use Spatie\Permission\Models\Role;
use App\Utilities\ErrorMessage;


class UserAuthRepository
{
    use Repository;

    protected $otpService;

    /**
     * The model being queried.
     *
     * @var UserAuth
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
        // Don't forget to update the model's name
        $this->model = app(UserAuth::class);
    }

    /**
     * Login
     */
    public function login($data)
    {
        $exp = Carbon::now()->addSeconds(3600);
        $token = Auth::guard('api')->attempt($data, ['exp' => $exp->timestamp]);
        if (! $token) {
            throw new JsonResponseException([
                'message' => 'Email ou mot de passe incorrect',
                'success' => false,
                'data' => null,
                'warning' => 'Vérifiez l\'adresse e-mail fournie ou le mot de passe',
            ], 401);

        }
              // Si l'authentification réussit
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expired_at' => Carbon::parse($exp)
                ->timezone('Africa/Porto-Novo')
                ->toDateTimeString(),
        ];

    }


 
    public function register($data)
    {
        try {
        DB::beginTransaction();
        $promoter= new Promoter();
        $promoter->lastname=$data['lastname'];
        $promoter->firstname=$data['firstname'];
        $promoter->phone=$data['phone'];
        $promoter->email=$data['email'];
        $promoter->save();
        
        $userData=[];
 
        $userData['name']=$data['lastname']." ".$data['firstname'];
        $userData['email']=$data['email'];
        $userData['promoter_id']=$promoter->id;
        $userData['password']= Hash::make($data['password']);
        $user=User::create($userData);

        $role = Role::whereName('Promoteur')->first();
        $user->assignRole([$role->id]);


                DB::commit();

        return $user;

        } catch (\Throwable $th) {
                    DB::rollback();

            throw new JsonResponseException([
                'message' => ErrorMessage::of($th),
                'success' => false,
                'data' => null,
                'warning' => null,
            ], 500);
        }

      

    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user()
    {

        $user = User::with(['roles.permissions', 'settings','promoter'])->whereId(Auth::id())->first();

        return $user;

    }

    public function user_permissions(Request $request, $data)
    {
        $projectId = $request->input('project_id');
        $label_names = $request->input('label_names'); // Ensure it's an array

        $up = UserProject::with([
            'roles',
            'roles.permissions' => function ($query) use ($label_names) {
                $query->whereIn('label_name', $label_names);
            },
        ])
            ->where('user_id', Auth::id())
            ->where('project_id', $projectId)
            ->first();

        return $up ? $up->roles : null;

    }

    public function changePassword($data)
    {
        $user = User::find(Auth::id());
        if (! Hash::check($data['old_password'], $user->password)) {
            throw new HttpResponseException(Common::failedValidation('Ancien mot de passe incorrect'));
        }

        $user->update(['password' => Hash::make($data['new_password'])]);

        return $user;
    }

    public function changeFirstPassword($data)
    {
        $user = User::find(Auth::id());

        $user->update([
            'password' => Hash::make($data['password']),
            'is_first_connexion' => 0,
        ]);

        return $user;
    }

    public function update($data)
    {
        $user = User::find(Auth::id());

        if (request()->hasFile('photo')) {
            FileStorage::deleteFile('public', $user->filename, 'avatars');
            $filename = FileStorage::setFile('public', request()->file('photo'), 'avatars', Str::slug($data['lastname'].'.'.$data['firstname'].'.'.time()));
            $data['photo'] = 'avatars/'.$filename;
        }

        $user->promoter->update([
            "lastname"=>$data['lastname'],
            "firstname"=>$data['firstname'],
            "phone"=>$data['phone'],
            "email"=>$data['email'],

        ]);

        $user->update([
            "lastname"=>$data['lastname'],
            "firstname"=>$data['firstname'],
            "email"=>$data['email'],

        ]);

        return $user;
    }

    public function logout(Request $request)
    {

        try {
            // Adds token to blacklist.
            $forever = true;
            JWTAuth::parseToken()->invalidate($forever);

            return null;

        } catch (TokenExpiredException $exception) {
            return response()->json([
                'error' => true,
                'message' => trans('auth.token.expired'),

            ], 401);
        } catch (TokenInvalidException $exception) {
            return response()->json([
                'error' => true,
                'message' => trans('auth.token.invalid'),
            ], 401);

        } catch (JWTException $exception) {
            return response()->json([
                'error' => true,
                'message' => trans('auth.token.missing'),
            ], 500);
        }
        $request->user()->currentAccessToken()->delete();

    }

    public function resetPassword($data)
    {
        $user = User::where('email', $data['email'])->first();

        $user->update([
            'password' => Hash::make($data['password']),
            'email' => $data['email'],
            'is_first_connexion' => true,
        ]);

        return $user;
    }

    public function sendResetPasswordLink($data)
    {
        $user = User::where('email', $data['email'])->first();
        $token = Str::random(64);
        $check = PasswordReset::where('email', $data['email'])->first();
        if ($check) {
            $check->token = $token;
            $check->created_at = Carbon::now();
            $check->save();
        } else {
            PasswordReset::create([
                'email' => $data['email'],
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

        }

        Mailer::sendSimple('emails.reset_password', ['user' => $user, 'token' => $token], 'Réinitialisation de mot de passe', $user->name, $user->email);

        return null;
    }

    public function recoveryPassword($data)
    {
        $checkToken = PasswordReset::where('token', $data['token'])->first();

        $user = User::whereEmail($checkToken->email);
        $user = $user->update(['password' => Hash::make($data['password'])]);
        $q = 'DELETE FROM password_reset_tokens where token = ?';
        DB::delete($q, [$data['token']]);

        return $user;
    }
}
