<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'code' => "001", 

            'name' => 'Admin Admin', 

            'email' => 'admin@cape.com',

            'password' => bcrypt('123')

        ]);

    

        $role = Role::whereName('admin')->first();
       /* $permissions = Permission::pluck('id','id')->all();
        $role->syncPermissions($permissions);*/
        $user->assignRole([$role->id]);
    }
}
