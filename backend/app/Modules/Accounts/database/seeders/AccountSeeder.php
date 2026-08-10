<?php

namespace App\Modules\Accounts\database\seeders;

use App\Modules\Accounts\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan Daniel',
            'email' => 'juan@example.com',
        ]);

        Account::factory()
            ->count(4)
            ->for($user)
            ->create();
    }
}
