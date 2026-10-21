<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Accounts\Models\Account;
use App\Modules\Settings\Http\Middleware\EnforceInactivityTimeout;
use App\Modules\Settings\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'currency' => 'COP',
            'budget_method' => '50_30_20',
            'auto_logout_inactive' => true,
            'inactivity_timeout_minutes' => 5,
        ], $overrides);
    }

    // Petición desde el frontend (cookies de sesión), como en el navegador.
    private function fromBrowser(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/configuracion',
        ]);
    }

    public function test_defaults_are_created_on_first_read(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/user/settings')
            ->assertOk()
            ->assertJsonPath('data.currency', 'COP')
            ->assertJsonPath('data.budget_method', '50_30_20')
            ->assertJsonPath('data.budget_percents', ['need' => 50, 'want' => 30, 'savings' => 20])
            ->assertJsonPath('data.auto_logout_inactive', true)
            ->assertJsonPath('data.inactivity_timeout_minutes', 5);

        $this->assertDatabaseCount('user_settings', 1);
    }

    public function test_me_includes_the_settings(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/auth/me')
            ->assertJsonPath('data.settings.currency', 'COP')
            ->assertJsonPath('data.settings.auto_logout_inactive', true);
    }

    public function test_it_updates_the_settings(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/user/settings', $this->payload([
                'currency' => 'USD',
                'budget_method' => 'custom',
                'budget_need_percent' => 60,
                'budget_want_percent' => 20,
                'budget_savings_percent' => 20,
                'auto_logout_inactive' => false,
                'inactivity_timeout_minutes' => 15,
            ]))
            ->assertOk()
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.budget_percents', ['need' => 60, 'want' => 20, 'savings' => 20])
            ->assertJsonPath('data.auto_logout_inactive', false)
            ->assertJsonPath('data.inactivity_timeout_minutes', 15);
    }

    public function test_it_validates_the_settings(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/user/settings', $this->payload([
                'currency' => 'BTC',
                'inactivity_timeout_minutes' => 3,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['currency', 'inactivity_timeout_minutes']);

        $this->putJson('/api/user/settings', $this->payload([
            'budget_method' => 'custom',
            'budget_need_percent' => 60,
            'budget_want_percent' => 30,
            'budget_savings_percent' => 20,
        ]))
            ->assertUnprocessable()
            ->assertJsonPath('errors.budget_need_percent.0', 'Los porcentajes deben sumar 100% (ahora suman 110%).');

        $this->putJson('/api/user/settings', $this->payload(['budget_method' => 'zero_based']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('budget_method');
    }

    public function test_going_back_to_50_30_20_keeps_the_custom_percents(): void
    {
        $this->actingAs($this->user)->putJson('/api/user/settings', $this->payload([
            'budget_method' => 'custom',
            'budget_need_percent' => 40,
            'budget_want_percent' => 30,
            'budget_savings_percent' => 30,
        ]));

        $this->putJson('/api/user/settings', $this->payload())
            ->assertJsonPath('data.budget_percents', ['need' => 50, 'want' => 30, 'savings' => 20])
            ->assertJsonPath('data.custom_percents', ['need' => 40, 'want' => 30, 'savings' => 30]);
    }

    public function test_custom_percents_change_the_budget_and_reports(): void
    {
        Account::factory()->create(['user_id' => $this->user->id, 'currency' => 'COP', 'is_active' => true]);

        $this->actingAs($this->user)->putJson('/api/budgets?period=2026-10', [
            'net_income' => 1000000,
            'currency' => 'COP',
        ])->assertOk();

        $this->putJson('/api/user/settings', $this->payload([
            'budget_method' => 'custom',
            'budget_need_percent' => 60,
            'budget_want_percent' => 10,
            'budget_savings_percent' => 30,
        ]));

        $rule = $this->getJson('/api/budgets?period=2026-10')->assertOk()->json('data.rule');

        $this->assertSame(60, $rule['need']['percent']);
        $this->assertEquals(600000, $rule['need']['limit']);
        $this->assertEquals(100000, $rule['want']['limit']);
        $this->assertEquals(300000, $rule['savings']['limit']);

        $this->getJson('/api/reports?date_from=2026-10-01&date_to=2026-10-31')
            ->assertOk()
            ->assertJsonPath('data.spending.by_category.need.recommended_percent', 60);
    }

    public function test_the_dashboard_uses_the_preferred_currency_when_the_user_has_it(): void
    {
        Account::factory()->count(2)->create(['user_id' => $this->user->id, 'currency' => 'COP', 'is_active' => true]);
        Account::factory()->create(['user_id' => $this->user->id, 'currency' => 'USD', 'is_active' => true]);

        $this->actingAs($this->user);

        // Antes: la moneda con más cuentas.
        $this->getJson('/api/dashboard')->assertJsonPath('data.currency', 'COP');

        $this->putJson('/api/user/settings', $this->payload(['currency' => 'USD']));
        $this->getJson('/api/dashboard')->assertJsonPath('data.currency', 'USD');

        // Sin cuentas en la moneda preferida, no se muestran totales vacíos: se usa la mayoritaria.
        $this->putJson('/api/user/settings', $this->payload(['currency' => 'MXN']));
        $this->getJson('/api/dashboard')->assertJsonPath('data.currency', 'COP');
    }

    public function test_inactive_sessions_are_closed_by_the_server(): void
    {
        $this->actingAs($this->user)
            ->fromBrowser()
            ->withSession([EnforceInactivityTimeout::SESSION_KEY => now()->subMinutes(6)->getTimestamp()])
            ->getJson('/api/accounts')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'session_inactive');

        $this->assertGuest('web');
    }

    public function test_activity_within_the_timeout_keeps_the_session(): void
    {
        $this->actingAs($this->user)
            ->fromBrowser()
            ->withSession([EnforceInactivityTimeout::SESSION_KEY => now()->subMinutes(4)->getTimestamp()])
            ->getJson('/api/accounts')
            ->assertOk()
            ->assertSessionHas(EnforceInactivityTimeout::SESSION_KEY, now()->getTimestamp());
    }

    public function test_disabling_the_option_or_a_longer_timeout_keeps_the_session(): void
    {
        UserSetting::query()->create(['user_id' => $this->user->id] + UserSetting::DEFAULTS)->update([
            'auto_logout_inactive' => false,
        ]);

        $this->actingAs($this->user)
            ->fromBrowser()
            ->withSession([EnforceInactivityTimeout::SESSION_KEY => now()->subHours(2)->getTimestamp()])
            ->getJson('/api/accounts')
            ->assertOk();

        UserSetting::query()->where('user_id', $this->user->id)->update([
            'auto_logout_inactive' => true,
            'inactivity_timeout_minutes' => 15,
        ]);

        $this->fromBrowser()
            ->withSession([EnforceInactivityTimeout::SESSION_KEY => now()->subMinutes(10)->getTimestamp()])
            ->getJson('/api/accounts')
            ->assertOk();
    }

    public function test_settings_are_private_to_each_user(): void
    {
        $other = User::factory()->create();

        $this->actingAs($other)->putJson('/api/user/settings', $this->payload(['currency' => 'EUR']))->assertOk();

        $this->actingAs($this->user)
            ->getJson('/api/user/settings')
            ->assertJsonPath('data.currency', 'COP');
    }

    public function test_guests_cannot_read_or_change_settings(): void
    {
        $this->getJson('/api/user/settings')->assertUnauthorized();
        $this->putJson('/api/user/settings', $this->payload())->assertUnauthorized();
    }
}
