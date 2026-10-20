<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Legal\Models\LegalAcceptance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /** Como un registro normal: el usuario ya aceptó los documentos legales vigentes. */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            foreach (config('legal.documents') as $document => $config) {
                LegalAcceptance::query()->create([
                    'user_id' => $user->id,
                    'document' => $document,
                    'version' => $config['version'],
                    'accepted_at' => now(),
                ]);
            }
        });
    }

    /** Usuario que todavía no ha aceptado (p. ej. creado antes de existir los documentos). */
    public function withoutLegalAcceptance(): static
    {
        return $this->afterCreating(
            fn (User $user) => LegalAcceptance::query()->where('user_id', $user->id)->delete()
        );
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
