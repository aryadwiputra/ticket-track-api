<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            'dashboard-access', 'dashboard-data',
            'permissions-access', 'permissions-data', 'permissions-create', 'permissions-update', 'permissions-delete',
            'roles-access', 'roles-data', 'roles-create', 'roles-update', 'roles-delete',
            'users-access', 'users-data', 'users-create', 'users-update', 'users-delete',
        ])->each(fn($item) => Permission::create(['name' => $item]));
    }
}
