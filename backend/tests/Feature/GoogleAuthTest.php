<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
            'services.frontend_url' => 'http://localhost:5173',
        ]);
    }

    private function fakeGoogle(array $overrides = []): void
    {
        $data = array_merge([
            'id' => 'google-123',
            'name' => 'Ana Gómez',
            'email' => 'Ana@Gmail.com',
            'email_verified' => true,
        ], $overrides);

        $googleUser = (new GoogleUser())
            ->setRaw(['email_verified' => $data['email_verified']])
            ->map(['id' => $data['id'], 'name' => $data['name'], 'email' => $data['email']]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_providers_endpoint_reports_whether_google_is_configured(): void
    {
        $this->getJson('/api/auth/providers')->assertOk()->assertJsonPath('data.google', true);

        config(['services.google.client_id' => null]);

        $this->getJson('/api/auth/providers')->assertJsonPath('data.google', false);
    }

    public function test_redirect_sends_the_user_to_google(): void
    {
        $this->get('/auth/google/redirect')
            ->assertRedirect()
            ->assertRedirectContains('accounts.google.com');
    }

    public function test_redirect_without_configuration_returns_to_login(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google/redirect')
            ->assertRedirect('http://localhost:5173/login?error=google_unavailable');
    }

    public function test_callback_registers_a_new_user_and_logs_in(): void
    {
        $this->fakeGoogle();

        $this->get('/auth/google/callback?code=abc&state=xyz')
            ->assertRedirect('http://localhost:5173/dashboard');

        $user = User::firstWhere('email', 'ana@gmail.com');

        $this->assertNotNull($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user, 'web');

        // Igual que un registro normal: arranca con sus categorías y con el recorrido pendiente.
        $this->assertSame(16, $user->hasMany(\App\Modules\Categories\Models\ExpenseCategory::class)->count());
        $this->assertNull($user->onboarding_completed_at);
    }

    public function test_callback_links_an_existing_account_with_the_same_email(): void
    {
        $existing = User::factory()->create(['email' => 'ana@gmail.com', 'password' => 'secreta123']);

        $this->fakeGoogle();

        $this->get('/auth/google/callback?code=abc')->assertRedirect('http://localhost:5173/dashboard');

        $this->assertSame(1, User::count());
        $this->assertSame('google-123', $existing->fresh()->google_id);
        $this->assertAuthenticatedAs($existing, 'web');
    }

    public function test_callback_finds_the_user_by_google_id_even_if_the_email_changed(): void
    {
        $existing = User::factory()->create(['email' => 'viejo@gmail.com', 'google_id' => 'google-123']);

        $this->fakeGoogle(['email' => 'nuevo@gmail.com']);

        $this->get('/auth/google/callback?code=abc');

        $this->assertSame(1, User::count());
        $this->assertAuthenticatedAs($existing, 'web');
    }

    public function test_unverified_google_emails_are_rejected(): void
    {
        User::factory()->create(['email' => 'ana@gmail.com']);

        $this->fakeGoogle(['email_verified' => false]);

        $this->get('/auth/google/callback?code=abc')
            ->assertRedirect('http://localhost:5173/login?error=google_email_unverified');

        $this->assertGuest('web');
        $this->assertNull(User::first()->google_id);
    }

    public function test_cancelling_on_google_returns_to_login(): void
    {
        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect('http://localhost:5173/login?error=google_cancelled');

        $this->assertGuest('web');
    }

    public function test_google_only_accounts_get_a_clear_message_on_password_login(): void
    {
        User::factory()->create(['email' => 'ana@gmail.com', 'password' => null, 'google_id' => 'google-123']);

        $this->withHeaders(['Origin' => 'http://localhost:5173', 'Referer' => 'http://localhost:5173/login'])
            ->postJson('/api/auth/login', ['email' => 'ana@gmail.com', 'password' => 'loquesea'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Esta cuenta se creó con Google. Usa "Continuar con Google" para entrar.');
    }

    public function test_google_id_is_never_exposed(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123']);

        $this->actingAs($user)
            ->getJson('/api/auth/me')
            ->assertJsonMissingPath('data.google_id')
            ->assertJsonPath('data.google_linked', true);
    }
}
