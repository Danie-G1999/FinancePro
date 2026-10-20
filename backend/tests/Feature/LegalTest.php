<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Legal\Models\LegalAcceptance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/registro',
        ]);
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Gómez',
            'email' => 'ana@correo.com',
            'password' => 'secreta123',
            'password_confirmation' => 'secreta123',
            'accept_terms' => true,
            'accept_privacy' => true,
        ], $overrides);
    }

    public function test_register_requires_both_documents(): void
    {
        $this->postJson('/api/auth/register', $this->registration([
            'accept_terms' => false,
            'accept_privacy' => null,
        ]))
            ->assertUnprocessable()
            ->assertJsonPath('errors.accept_terms.0', 'Debes aceptar los Términos y Condiciones.')
            ->assertJsonPath('errors.accept_privacy.0', 'Debes autorizar el tratamiento de tus datos personales.');

        $this->assertSame(0, User::count());
    }

    public function test_register_stores_proof_of_the_authorization(): void
    {
        $this->withHeader('User-Agent', 'Navegador de prueba')
            ->postJson('/api/auth/register', $this->registration())
            ->assertCreated()
            ->assertJsonPath('data.legal.pending', []);

        $user = User::first();

        foreach (['terms', 'privacy'] as $document) {
            $acceptance = LegalAcceptance::where('user_id', $user->id)->where('document', $document)->first();

            $this->assertNotNull($acceptance, "Falta la aceptación de {$document}.");
            $this->assertSame(config("legal.documents.{$document}.version"), $acceptance->version);
            $this->assertNotNull($acceptance->accepted_at);
            $this->assertSame('127.0.0.1', $acceptance->ip_address);
            $this->assertSame('Navegador de prueba', $acceptance->user_agent);
        }
    }

    public function test_users_without_acceptance_are_blocked_until_they_accept(): void
    {
        $user = User::factory()->withoutLegalAcceptance()->create();

        $this->actingAs($user);

        $this->getJson('/api/auth/me')
            ->assertSuccessful()
            ->assertJsonPath('data.legal.pending', ['terms', 'privacy']);

        foreach (['accounts', 'expenses', 'dashboard', 'profile'] as $resource) {
            $this->getJson("/api/{$resource}")
                ->assertForbidden()
                ->assertJsonPath('code', 'legal_acceptance_required');
        }

        // Aceptar solo uno no basta.
        $this->postJson('/api/legal/accept', ['documents' => ['terms']])
            ->assertSuccessful()
            ->assertJsonPath('data.legal.pending', ['privacy']);

        $this->getJson('/api/accounts')->assertForbidden();

        $this->postJson('/api/legal/accept', ['documents' => ['privacy']])
            ->assertJsonPath('data.legal.pending', []);

        $this->getJson('/api/accounts')->assertOk();
    }

    public function test_accepting_twice_keeps_the_original_record(): void
    {
        $user = User::factory()->withoutLegalAcceptance()->create();

        $this->actingAs($user)->postJson('/api/legal/accept', ['documents' => ['terms', 'privacy']]);
        $first = LegalAcceptance::where('user_id', $user->id)->orderBy('id')->first()->accepted_at;

        $this->travel(10)->minutes();
        $this->postJson('/api/legal/accept', ['documents' => ['terms', 'privacy']]);

        $this->assertSame(2, LegalAcceptance::where('user_id', $user->id)->count());
        $this->assertEquals($first, LegalAcceptance::where('user_id', $user->id)->orderBy('id')->first()->accepted_at);
    }

    public function test_publishing_a_new_version_asks_everyone_to_accept_again(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/accounts')->assertOk();

        config(['legal.documents.privacy.version' => '2.0']);

        $this->getJson('/api/auth/me')
            ->assertJsonPath('data.legal.pending', ['privacy'])
            ->assertJsonPath('data.legal.documents.privacy.accepted_version', '1.0');

        $this->getJson('/api/accounts')->assertForbidden();

        $this->postJson('/api/legal/accept', ['documents' => ['privacy']]);

        $this->getJson('/api/accounts')->assertOk();

        // El historial conserva las dos versiones aceptadas.
        $this->assertSame(['1.0', '2.0'], LegalAcceptance::where('user_id', $user->id)
            ->where('document', 'privacy')->orderBy('id')->pluck('version')->all());
    }

    public function test_unknown_documents_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/legal/accept', ['documents' => ['marketing']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('documents.0');
    }

    public function test_guests_cannot_accept(): void
    {
        $this->postJson('/api/legal/accept', ['documents' => ['terms']])->assertUnauthorized();
    }
}
