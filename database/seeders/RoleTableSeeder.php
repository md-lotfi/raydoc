<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rome = Role::updateOrCreate(
            ['name' => config('constants.ROLES.ADMIN')],
            ['name' => config('constants.ROLES.ADMIN'), 'guard_name' => 'web']
        );
        // $role->syncPermissions(['edit articles', 'delete articles']);
        Role::updateOrCreate(
            ['name' => config('constants.ROLES.ASSISTANT')],
            ['name' => config('constants.ROLES.ASSISTANT'), 'guard_name' => 'web']
        );
        Role::updateOrCreate(
            ['name' => config('constants.ROLES.DOCTOR')],
            ['name' => config('constants.ROLES.DOCTOR'), 'guard_name' => 'web']
        );

    }
}
