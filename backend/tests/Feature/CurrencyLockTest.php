<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Accounts\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los ingresos y gastos usan la moneda de su cuenta: cambiarla con movimientos alteraría
 * los valores sin tipo de cambio. Lo mismo con los pagos de deudas y los aportes a metas.
 */
class CurrencyLockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->account = Account::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Ahorros',
            'type' => 'savings',
            'currency' => 'COP',
            'initial_balance' => 5000000,
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
    }

    private function accountPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ahorros',
            'type' => 'savings',
            'initial_balance' => 5000000,
            'currency' => 'COP',
            'is_active' => true,
        ], $overrides);
    }

    private function createDebt(): int
    {
        return $this->postJson('/api/debts', [
            'name' => 'Préstamo',
            'type' => 'personal_loan',
            'total_amount' => 900000,
            'installments' => 3,
            'payment_frequency' => 'monthly',
            'payment_day' => 5,
            'currency' => 'COP',
        ])->assertCreated()->json('data.id');
    }

    private function createGoal(): int
    {
        return $this->postJson('/api/goals', [
            'name' => 'Viaje',
            'target_amount' => 1000000,
            'currency' => 'COP',
        ])->assertCreated()->json('data.id');
    }

    private function expense(array $overrides = []): void
    {
        $this->postJson('/api/expenses', array_merge([
            'account_id' => $this->account->id,
            'description' => 'Movimiento',
            'amount' => 100000,
            'spent_at' => '2026-10-21',
        ], $overrides))->assertCreated();
    }

    // ---------------- Cuentas ----------------

    public function test_an_account_without_movements_can_fix_its_currency(): void
    {
        $this->getJson('/api/accounts')->assertJsonPath('data.0.currency_locked', false);

        $this->putJson("/api/accounts/{$this->account->id}", $this->accountPayload(['currency' => 'USD']))
            ->assertOk()
            ->assertJsonPath('data.currency', 'USD');
    }

    public function test_an_account_with_movements_cannot_change_its_currency(): void
    {
        $this->postJson('/api/incomes', [
            'account_id' => $this->account->id,
            'description' => 'Salario',
            'amount' => 2000000,
            'received_at' => '2026-10-01',
        ])->assertCreated();

        $this->getJson('/api/accounts')->assertJsonPath('data.0.currency_locked', true);

        $this->putJson("/api/accounts/{$this->account->id}", $this->accountPayload(['currency' => 'USD']))
            ->assertUnprocessable()
            ->assertJsonPath('errors.currency.0', 'No puedes cambiar la moneda: esta cuenta ya tiene movimientos o ingresos programados.');

        $this->assertSame('COP', $this->account->fresh()->currency);
    }

    public function test_an_account_with_only_expenses_or_scheduled_incomes_is_locked_too(): void
    {
        $other = Account::factory()->create(['user_id' => $this->user->id, 'currency' => 'COP', 'is_active' => true]);

        $this->expense(['budget_type' => 'need', 'cost_type' => 'variable', 'expense_category_id' => $this->category()]);

        $this->postJson('/api/recurring-incomes', [
            'account_id' => $other->id,
            'description' => 'Salario mensual',
            'amount' => 1000000,
            'type' => 'salary',
            'day_of_month' => 30,
            'start_date' => '2026-10-30',
        ])->assertCreated();

        foreach ([$this->account, $other] as $account) {
            $this->putJson("/api/accounts/{$account->id}", $this->accountPayload(['currency' => 'EUR']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('currency');
        }
    }

    public function test_other_fields_of_a_locked_account_can_still_be_edited(): void
    {
        $this->expense(['budget_type' => 'need', 'cost_type' => 'variable', 'expense_category_id' => $this->category()]);

        $this->putJson("/api/accounts/{$this->account->id}", $this->accountPayload(['name' => 'Ahorros principal']))
            ->assertOk()
            ->assertJsonPath('data.name', 'Ahorros principal')
            ->assertJsonPath('data.currency', 'COP');
    }

    // ---------------- Deudas ----------------

    public function test_a_debt_with_payments_cannot_change_its_currency(): void
    {
        $debtId = $this->createDebt();

        $this->putJson("/api/debts/{$debtId}", $this->debtPayload(['currency' => 'USD']))->assertOk();
        $this->putJson("/api/debts/{$debtId}", $this->debtPayload(['currency' => 'COP']))->assertOk();

        $this->expense(['debt_id' => $debtId]);

        $this->getJson("/api/debts/{$debtId}")->assertJsonPath('data.currency_locked', true);

        $this->putJson("/api/debts/{$debtId}", $this->debtPayload(['currency' => 'USD']))
            ->assertUnprocessable()
            ->assertJsonPath('errors.currency.0', 'No puedes cambiar la moneda: esta deuda ya tiene pagos registrados.');
    }

    // ---------------- Metas ----------------

    public function test_a_goal_without_contributions_can_fix_its_currency_but_not_after(): void
    {
        $goalId = $this->createGoal();

        $this->getJson('/api/goals')->assertJsonPath('data.0.currency_locked', false);

        $this->putJson("/api/goals/{$goalId}", ['name' => 'Viaje', 'target_amount' => 1000000, 'currency' => 'USD'])
            ->assertOk();
        $this->putJson("/api/goals/{$goalId}", ['name' => 'Viaje', 'target_amount' => 1000000, 'currency' => 'COP'])
            ->assertOk();

        $this->expense(['goal_id' => $goalId]);

        $this->getJson('/api/goals')->assertJsonPath('data.0.currency_locked', true);

        // Antes solo lo bloqueaba el formulario; ahora también el servidor.
        $this->putJson("/api/goals/{$goalId}", ['name' => 'Viaje', 'target_amount' => 1000000, 'currency' => 'USD'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.currency.0', 'No puedes cambiar la moneda: esta meta ya tiene aportes.');
    }

    private function debtPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Préstamo',
            'type' => 'personal_loan',
            'total_amount' => 900000,
            'installments' => 3,
            'payment_frequency' => 'monthly',
            'payment_day' => 5,
            'currency' => 'COP',
        ], $overrides);
    }

    private function category(): int
    {
        return $this->getJson('/api/expense-categories?budget_type=need')->json('data.0.id');
    }
}
