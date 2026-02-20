<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'              => 'Administrador',
            'document'          => '123456789',
            'email'             => 'admin@example.com',
            'password'          => Hash::make('123456789'),
            'role'              => 'admin',
            'is_approved'       => true,
            'email_verified_at' => now(),
        ]);
    }
}