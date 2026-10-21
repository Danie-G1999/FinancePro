<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['password' => 'actual123']);

        $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/perfil',
        ]);
    }

    private function change(array $data)
    {
        return $this->actingAs($this->user)->putJson('/api/profile/password', $data);
    }

    public function test_it_changes_the_password(): void
    {
        $this->change([
            'current_password' => 'actual123',
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Contraseña actualizada. Cerramos tus otras sesiones abiertas.');

        $fresh = $this->user->fresh();

        $this->assertTrue(Hash::check('nueva4567', $fresh->password));
        $this->assertFalse(Hash::check('actual123', $fresh->password));
    }

    public function test_the_new_password_works_for_login_and_the_old_one_does_not(): void
    {
        $this->change([
            'current_password' => 'actual123',
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])->assertOk();

        // Como una visita nueva: sin la sesión ni el guard de la petición anterior.
        $this->app['auth']->guard('web')->logout();
        $this->app['auth']->forgetGuards();
        $this->app['auth']->shouldUse('web');

        $this->postJson('/api/auth/login', ['email' => $this->user->email, 'password' => 'actual123'])
            ->assertUnprocessable();

        $this->postJson('/api/auth/login', ['email' => $this->user->email, 'password' => 'nueva4567'])
            ->assertOk();
    }

    public function test_it_rejects_a_wrong_current_password(): void
    {
        $this->change([
            'current_password' => 'equivocada1',
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.current_password.0', 'La contraseña actual no es correcta.');

        $this->assertTrue(Hash::check('actual123', $this->user->fresh()->password));
    }

    public function test_it_validates_the_new_password(): void
    {
        $this->change([
            'current_password' => 'actual123',
            'password' => 'actual123',
            'password_confirmation' => 'actual123',
        ])->assertJsonPath('errors.password.0', 'La nueva contraseña debe ser distinta de la actual.');

        $this->change([
            'current_password' => 'actual123',
            'password' => 'nueva4567',
            'password_confirmation' => 'otra4567',
        ])->assertJsonPath('errors.password.0', 'Las contraseñas nuevas no coinciden.');

        $this->change([
            'current_password' => 'actual123',
            'password' => 'sololetras',
            'password_confirmation' => 'sololetras',
        ])->assertJsonPath('errors.password.0', 'La contraseña debe incluir al menos un número.');

        $this->change([
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])->assertJsonPath('errors.current_password.0', 'Escribe tu contraseña actual.');
    }

    public function test_other_sessions_are_closed_but_the_current_one_stays(): void
    {
        config(['session.driver' => 'database']);

        DB::table('sessions')->insert([
            ['id' => 'otro-equipo', 'user_id' => $this->user->id, 'payload' => '', 'last_activity' => now()->timestamp],
            ['id' => 'otro-usuario', 'user_id' => User::factory()->create()->id, 'payload' => '', 'last_activity' => now()->timestamp],
        ]);

        $this->change([
            'current_password' => 'actual123',
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])->assertOk();

        $this->assertDatabaseMissing('sessions', ['id' => 'otro-equipo']);
        $this->assertDatabaseHas('sessions', ['id' => 'otro-usuario']);

        // La sesión con la que cambió la contraseña sigue funcionando.
        $this->getJson('/api/auth/me')->assertSuccessful();
    }

    public function test_google_only_accounts_can_create_a_password_without_a_current_one(): void
    {
        $google = User::factory()->create(['password' => null, 'google_id' => 'google-1']);

        $this->actingAs($google)
            ->getJson('/api/auth/me')
            ->assertJsonPath('data.has_password', false);

        $this->putJson('/api/profile/password', [
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Contraseña creada. Ahora también puedes entrar con tu correo y contraseña.');

        $this->assertTrue(Hash::check('nueva4567', $google->fresh()->password));
        $this->getJson('/api/auth/me')->assertJsonPath('data.has_password', true);
    }

    public function test_guessing_the_current_password_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->change([
                'current_password' => "intento{$i}x",
                'password' => 'nueva4567',
                'password_confirmation' => 'nueva4567',
            ])->assertUnprocessable();
        }

        $this->change([
            'current_password' => 'actual123',
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])->assertStatus(429);
    }

    public function test_guests_cannot_change_passwords(): void
    {
        $this->putJson('/api/profile/password', [
            'password' => 'nueva4567',
            'password_confirmation' => 'nueva4567',
        ])->assertUnauthorized();
    }
}
