<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Auth\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // Sanctum solo abre sesión para peticiones que vienen del frontend (dominio "stateful").
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/login',
        ]);
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Gómez',
            'email' => 'Ana@Correo.com',
            'password' => 'secreta123',
            'password_confirmation' => 'secreta123',
            'accept_terms' => true,
            'accept_privacy' => true,
        ], $overrides);
    }

    public function test_register_creates_the_user_logs_in_and_seeds_categories(): void
    {
        $this->postJson('/api/auth/register', $this->registration())
            ->assertCreated()
            ->assertJsonPath('data.email', 'ana@correo.com');

        $this->assertAuthenticated('web');

        $this->getJson('/api/auth/me')->assertSuccessful()->assertJsonPath('data.name', 'Ana Gómez');

        $this->assertCount(16, $this->getJson('/api/expense-categories')->json('data'));

        // Un usuario recién registrado arranca sin datos financieros.
        $this->assertSame([], $this->getJson('/api/accounts')->json('data'));
    }

    public function test_register_validates_in_spanish(): void
    {
        User::factory()->create(['email' => 'ana@correo.com']);

        $this->postJson('/api/auth/register', $this->registration([
            'password' => 'solotexto',
            'password_confirmation' => 'otra',
        ]))
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Ya existe una cuenta con este correo.')
            ->assertJsonPath('errors.password.0', 'Las contraseñas no coinciden.');

        $this->postJson('/api/auth/register', $this->registration([
            'email' => 'nuevo@correo.com',
            'password' => 'solotexto',
            'password_confirmation' => 'solotexto',
        ]))
            ->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'La contraseña debe incluir al menos un número.');
    }

    public function test_login_ignores_email_case(): void
    {
        User::factory()->create(['email' => 'ana@correo.com', 'password' => 'secreta123']);

        $this->postJson('/api/auth/login', ['email' => ' ANA@correo.com ', 'password' => 'secreta123'])
            ->assertOk();

        $this->assertAuthenticated('web');
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        User::factory()->create(['email' => 'ana@correo.com', 'password' => 'secreta123']);

        for ($i = 0; $i < AuthServiceProvider::LOGIN_ATTEMPTS_PER_MINUTE; $i++) {
            $this->postJson('/api/auth/login', ['email' => 'ana@correo.com', 'password' => 'mala'])
                ->assertUnprocessable();
        }

        // Ni siquiera la contraseña correcta pasa mientras dura el bloqueo.
        $response = $this->postJson('/api/auth/login', ['email' => 'ana@correo.com', 'password' => 'secreta123'])
            ->assertStatus(429);

        $this->assertStringStartsWith('Demasiados intentos.', $response->json('message'));
        $this->assertGuest('web');
    }

    public function test_register_is_throttled_per_ip(): void
    {
        for ($i = 0; $i < AuthServiceProvider::REGISTER_ATTEMPTS_PER_HOUR; $i++) {
            $this->postJson('/api/auth/register', $this->registration(['email' => "user{$i}@correo.com"]));

            $this->app['auth']->guard('web')->logout();
        }

        $this->postJson('/api/auth/register', $this->registration(['email' => 'otro@correo.com']))
            ->assertStatus(429);
    }

    public function test_new_users_see_the_onboarding_until_they_complete_it(): void
    {
        $this->postJson('/api/auth/register', $this->registration())
            ->assertJsonPath('data.onboarding_completed', false);

        $this->postJson('/api/auth/onboarding/complete')
            ->assertSuccessful()
            ->assertJsonPath('data.onboarding_completed', true);

        $completedAt = User::first()->onboarding_completed_at;
        $this->assertNotNull($completedAt);

        // Repetir el recorrido desde el menú no cambia la fecha original.
        $this->travel(5)->minutes();
        $this->postJson('/api/auth/onboarding/complete')->assertSuccessful();
        $this->assertEquals($completedAt, User::first()->onboarding_completed_at);
    }

    public function test_guests_cannot_complete_the_onboarding(): void
    {
        $this->postJson('/api/auth/onboarding/complete')->assertUnauthorized();
    }
}
