<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('admin123'),
        ]);
        $user->assignRole(config('constants.ROLES.ADMIN'));

        $validPermissions = Permission::whereIn('name', getAdminPermissions())->pluck('name');
        $user->givePermissionTo($validPermissions);

        $user = User::updateOrCreate([
            'email' => 'assistant@admin.com',
        ], [
            'name' => 'Super Assistant',
            'password' => Hash::make('assistant123'),
        ]);
        $user->assignRole(config('constants.ROLES.ASSISTANT'));

        $validPermissions = Permission::whereIn('name', getAssistantPermissions())->pluck('name');
        $user->givePermissionTo($validPermissions);

        $user = User::updateOrCreate([
            'email' => 'doctor@admin.com',
        ], [
            'name' => 'Super Doctor',
            'password' => Hash::make('doctor123'),
        ]);
        $user->assignRole(config('constants.ROLES.DOCTOR'));

        $validPermissions = Permission::whereIn('name', getDoctorPermissions())->pluck('name');
        $user->givePermissionTo($validPermissions);

    }
}
