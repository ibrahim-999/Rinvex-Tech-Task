<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@acme.test'],
            [
                'name' => 'Admin',
                'email' => 'admin@acme.test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
