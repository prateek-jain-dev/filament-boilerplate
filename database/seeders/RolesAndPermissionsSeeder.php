<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Assuming your User model namespace

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view reports']);
        // Add other resource permissions (e.g., create post, edit post, delete post)

        // 2. Create roles and assign permissions
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $editorRole = Role::create(['name' => 'Editor']);
        $viewerRole = Role::create(['name' => 'Viewer']);

        // Assign all permissions to Super Admin (implicitly by granting all, or explicitly for clarity)
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign specific permissions to Editor
        $editorRole->givePermissionTo(['view dashboard', 'manage users']);

        // Assign specific permissions to Viewer
        $viewerRole->givePermissionTo(['view dashboard', 'view reports']);

        // 3. Assign the Super Admin role to your user
        $user = User::where('email', 'admin@example.com')->first();
        if ($user) {
            $user->assignRole($superAdminRole);
        }
    }
}