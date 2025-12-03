<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = getAdminPermissions();
        // Create or update permissions
        foreach ($permissions as $permission) {
            // Find or create the permission with the given name
            Permission::updateOrCreate(
                ['name' => $permission], // Attributes to find the record
                ['guard_name' => 'web']  // Attributes to update or create
            );
        }
    }
}
