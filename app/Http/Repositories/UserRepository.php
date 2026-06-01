<?php

namespace App\Http\Repositories;

use App\Events\ChangeStatutAgentEvent;
use App\Jobs\SendEmailJob;
use App\Models\Municipality;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserProject;
use App\Services\EquipeService;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var User
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(User::class);
    }

    /**
     * Check if user exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all users with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = User::ignoreRequest(['per_page',  'role'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with('roles')
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    /**
     * Get a specific user by id
     */
    public function get($id)
    {

                 return $this->findOrFail($id);


    }


    /**
     * Store a new user
     */
    public function makeStore($data): User
    {
        if (request()->hasFile('photo')) {
            $filename = FileStorage::setFile('public', request()->file('photo'), 'avatars', Str::slug($data['lastname'].'.'.$data['firstname'].'.'.time()));
            $data['photo'] = 'avatars/'.$filename;
        }

        $role = $data['role'];
        unset($data['role']);
        $password = "cape@2025";//Str::random(8);
        $data['password'] = Hash::make($password);
        $data['name']=$data['lastname']." ".$data['firstname'];
        $model = new User($data);
        $model->save();

        $role = Role::firstOrCreate(['name' => $role]);
        $model->assignRole($role);

        //Mailer::sendSimple('emails.new_account', ['user' => $model, 'password' => $password], 'Identifiant de connexion', $model->name, $model->email);

        // SendEmailJob::dispatch($model, $password);
        return $model;
    }

    
    /**
     * Update an existing user
     */
    public function makeUpdate($id, $data): User
    {
        $model = User::findOrFail($id);

        if (request()->hasFile('photo')) {
            FileStorage::deleteFile('public', $model->photo);
            $filename = FileStorage::setFile('public', request()->file('photo'), 'avatars', Str::slug($data['lastname'].'.'.$data['firstname'].'.'.time()));
            $data['photo'] = 'avatars/'.$filename;
        }

        $role = null;
        if (array_key_exists('role', $data)) {
            $role = $data['role'];
            unset($data['role']);
        }

        if (! empty($data['lastname']) && ! empty($data['firstname'])) {
            $data['name'] = $data['lastname'].' '.$data['firstname'];
        }

        $model->update($data);

        if ($role) {
            $role = Role::firstOrCreate(['name' => $role]);
            $model->syncRoles([$role]);
        }

        return $model;
    }

    /**
     * Delete a user
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    public function resetPasswordByAdmin($id)
    {
        $user = User::findOrFail($id);
        $password = Str::random(10);

        $user->update([
            'password' => Hash::make($password),
            'is_first_connexion' => true,
        ]);

        Mailer::sendSimple(
            'emails.admin_reset_password',
            ['user' => $user, 'password' => $password],
            'Réinitialisation de votre mot de passe',
            $user->name,
            $user->email
        );

        return $user;
    }

    /**
     * Get the latest users
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    public function setStatus($id, $status)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => $status]);

        return $user;
    }

    /**
     * Search for users by name, email, or code
     */
    public function search($term)
    {
        $query = User::query(); // Start with an empty query
        $attrs = ['name', 'email', 'code']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }


    public function signCode(Request $request)
    {
        $datas = $request->all();
        unset($datas['sign_code_confirm']); 
        $datas['sign_code'] = Hash::make($datas['sign_code']); // hash du code

        $user = Auth::user();
        $user->update($datas);

        return $user;
    }


    public function changeUserStatus(Request $request)
    {
        $user = User::find($request->user_id);
        $user->status = $request->status;
        $user->save();

        return $user;
    }


}
