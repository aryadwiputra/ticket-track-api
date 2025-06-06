<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => "Super Admin",
            "email" => "superadmin@example.com",
            "password" => bcrypt("password"),
        ]);

        User::create([
            "name" => "Role Example",
            "email" => "roleexample@example.com",
            "password" => bcrypt("password"),
        ]);
    }
}
