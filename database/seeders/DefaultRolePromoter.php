<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class DefaultRolePromoter extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $role = Role::create(['name' => 'Promoteur']);

    }
}
