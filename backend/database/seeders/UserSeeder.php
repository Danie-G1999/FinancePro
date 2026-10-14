<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // La contraseña se encripta con el cast "hashed" del modelo.
        User::updateOrCreate(
            ['email' => 'jg585480@gmail.com'],
            [
                'name' => 'Juan Daniel Guzman',
                'password' => '99040712565',
            ]
        );
    }
}
