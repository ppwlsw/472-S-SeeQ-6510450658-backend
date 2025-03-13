<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(100)->create();
        User::create([
            "name" => "Admin",
            "email" => "admin@admin.com",
            "password" => Hash::make("password"),
            "role" => "ADMIN",
            "email_verified_at" => now(),
        ]);
        for($x = 1; $x <= 5; $x++){
            User::create([
                "name" => "user$x",
                "email" => "user$x@user.com",
                "password" => Hash::make("password"),
                "role" => "CUSTOMER",
                "email_verified_at" => now(),
            ]);
        }

    }
}
