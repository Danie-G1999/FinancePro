<?php

namespace App\Modules\Accounts\database\factories;

use App\Modules\Accounts\Enums\AccountType;
use App\Modules\Accounts\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'Cuenta principal',
                'Cuenta de ahorros',
                'Efectivo',
                'Nequi',
                'Cuenta para gastos',
            ]),
            'type' => fake()->randomElement(AccountType::cases()),
            'institution' => fake()->randomElement([
                'Bancolombia',
                'Davivienda',
                'Banco de Bogotá',
                'Nequi',
                'Daviplata',
                null,
            ]),
            'initial_balance' => fake()->randomFloat(
                2,
                0,
                10000000
            ),
            'currency' => 'COP',
            'is_active' => true,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
