<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create(['name' => 'admin']);
        $role = Role::create(['name' => 'cape']);
        $role = Role::create(['name' => 'cps']);
        $role = Role::create(['name' => 'ddasm']);
        $role = Role::create(['name' => 'dfea']);
        $role = Role::create(['name' => 'ministre']);
        $permission = Permission::create(['name' => 'ajout']);
        $permission = Permission::create(['name' => 'modification']);
        $permission = Permission::create(['name' => 'consultation']);
        $permission = Permission::create(['name' => 'suppression']);
        $permission = Permission::create(['name' => 'transmission']);
        $permission = Permission::create(['name' => 'publication']);
    }
}
