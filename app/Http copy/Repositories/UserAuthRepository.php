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

                DB::commit();

        return $user;

        } catch (\Throwable $th) {
                    DB::rollback();

            throw new JsonResponseException([
                'message' => $th->getMessage(),
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

        $user = User::with(['roles.permissions', 'settings'])->whereId(Auth::id())->first();

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

        $user->update($data);

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

    public function getBrowser(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        $agent = new \Jenssegers\Agent\Agent;
        $agent->setUserAgent($userAgent);

        return $agent->browser();
    }

    public function notify($request)
    {

        $canal = $request->canal;
        if (array_search('PUSH', $canal) != null) {
            $canal[array_search('PUSH', $canal)] = FcmChannel::class;
        }
        if (array_search('SMS', $canal) != null) {
            $canal[array_search('SMS', $canal)] = TwilioChannel::class;
        }
        info($canal);

        try {
            switch ($request->category) {
                case 'election-pr-closed':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new ElectionPrClosedNotification($canal, $request->title, $request->content));
                    break;
                case 'next-activity':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new ElectionPrClosedNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-competion-register-closing':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-competion-step-closing':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'change-pr-state':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'etablissement-election-pr-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-pr-question':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-evaluation-re':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-pr-question-animatrice':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-pr-question-re':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-pr-question':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-status-pr-question-animatrice':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-status-pr-question':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-status-pr-question-responsable':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-plan-action':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-point-capitalisation':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-supervision-activite':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-monthly-report':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'comment-monthly-report':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 're-comment-monthly-report':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-gap-negative':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-officer-payment':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-animarice-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-animarice-validated':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-re-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-pr-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'prime-pr':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content, $request->file));
                    break;
                case 'change-status-agent':
                    info($request->users);
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;

                default:
                    // code...
                    break;
            }

            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function addPushToken($data)
    {
        return User::find($data['user_id'])->update(['push_token' => $data['push_token']]);
    }

    public function deletePushToken($id)
    {
        return User::find($id)->update(['push_token' => null]);
    }
}
