<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'super-admin']);

        $permission_permissions = Permission::where('name', 'like', '%permissions%')->get();
        $permission_role = Role::create(['name' => 'permissions-access']);
        $permission_role->givePermissionTo($permission_permissions);

        $role_permissions = Permission::where('name', 'like', '%roles%')->get();
        $role_role = Role::create(['name' => 'roles-access']);
        $role_role->givePermissionTo($role_permissions);

        $user_permissions = Permission::where('name', 'like', '%users%')->get();
        $user_role = Role::create(['name' => 'users-access']);
        $user_role->givePermissionTo($user_permissions);

        $user_super_admin = User::where('email', 'superadmin@example.com')->first();

        if ($user_super_admin) {
            $user_super_admin->assignRole('super-admin');
        }

        $user_role_example = User::where('email', 'roleexample@example.com')->first();

        if ($user_role_example) {
            $user_role_example->assignRole('permissions-access');
            $user_role_example->assignRole('roles-access');
            $user_role_example->assignRole('users-access');
        }
    }
}
