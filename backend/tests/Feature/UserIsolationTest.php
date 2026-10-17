<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Verifica que ningún usuario pueda ver, editar ni usar los datos financieros de otro. */
class UserIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $intruder;

    /** @var array<string, int> */
    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->intruder = User::factory()->create();

        $this->actingAs($this->owner);

        $this->ids['account'] = $this->postJson('/api/accounts', [
            'name' => 'Cuenta del dueño',
            'type' => 'savings',
            'initial_balance' => 1000000,
            'currency' => 'COP',
            'is_active' => true,
        ])->assertCreated()->json('data.id');

        $this->ids['income'] = $this->postJson('/api/incomes', [
            'account_id' => $this->ids['account'],
            'description' => 'Salario',
            'amount' => 500000,
            'received_at' => '2026-10-01',
        ])->assertCreated()->json('data.id');

        $this->ids['recurring'] = $this->postJson('/api/recurring-incomes', [
            'account_id' => $this->ids['account'],
            'description' => 'Salario mensual',
            'amount' => 500000,
            'type' => 'salary',
            'day_of_month' => 30,
            'start_date' => '2026-10-30',
        ])->assertCreated()->json('data.id');

        $this->ids['debt'] = $this->postJson('/api/debts', [
            'name' => 'Préstamo',
            'type' => 'personal_loan',
            'total_amount' => 300000,
            'installments' => 3,
            'payment_frequency' => 'monthly',
            'payment_day' => 5,
            'currency' => 'COP',
        ])->assertCreated()->json('data.id');

        $this->ids['goal'] = $this->postJson('/api/goals', [
            'name' => 'Viaje',
            'target_amount' => 200000,
            'currency' => 'COP',
        ])->assertCreated()->json('data.id');

        $this->ids['category'] = $this->getJson('/api/expense-categories?budget_type=need')
            ->json('data.0.id');

        $this->ids['expense'] = $this->postJson('/api/expenses', [
            'account_id' => $this->ids['account'],
            'description' => 'Mercado',
            'amount' => 80000,
            'spent_at' => '2026-10-02',
            'budget_type' => 'need',
            'cost_type' => 'variable',
            'expense_category_id' => $this->ids['category'],
        ])->assertCreated()->json('data.id');

        $this->putJson('/api/budgets?period=2026-10', [
            'net_income' => 2000000,
            'currency' => 'COP',
        ])->assertOk();

        $this->actingAs($this->intruder);
    }

    public function test_lists_only_show_own_data(): void
    {
        foreach (['accounts', 'incomes', 'recurring-incomes', 'debts', 'goals', 'expenses', 'transactions'] as $resource) {
            $this->assertSame(
                [],
                $this->getJson("/api/{$resource}")->assertOk()->json('data'),
                "El listado /api/{$resource} muestra datos de otro usuario."
            );
        }

        $foreignCategories = array_intersect(
            array_column($this->getJson('/api/expense-categories')->json('data'), 'id'),
            [$this->ids['category']]
        );

        $this->assertSame([], $foreignCategories);
    }

    public function test_cannot_read_update_or_delete_foreign_records(): void
    {
        $routes = [
            'accounts' => 'account',
            'incomes' => 'income',
            'debts' => 'debt',
            'goals' => 'goal',
            'expenses' => 'expense',
            'expense-categories' => 'category',
        ];

        foreach ($routes as $resource => $key) {
            $url = "/api/{$resource}/{$this->ids[$key]}";

            $this->getJson($url)->assertNotFound();
            $status = $this->putJson($url, ['name' => 'x', 'description' => 'x'])->status();
            $this->assertContains($status, [404, 422], "PUT {$url} respondió {$status}.");
            $this->deleteJson($url)->assertNotFound();
        }

        $this->getJson("/api/debts/{$this->ids['debt']}/payments")->assertNotFound();
        $this->deleteJson("/api/recurring-incomes/{$this->ids['recurring']}")->assertNotFound();
        $this->patchJson("/api/recurring-incomes/{$this->ids['recurring']}/status", ['is_active' => false])->assertNotFound();

        $this->assertDatabaseMissing('accounts', ['name' => 'x']);
        $this->assertDatabaseMissing('debts', ['name' => 'x']);
        $this->assertDatabaseMissing('goals', ['name' => 'x']);
        $this->assertDatabaseMissing('incomes', ['description' => 'x']);
        $this->assertDatabaseMissing('expenses', ['description' => 'x']);

        foreach (['accounts', 'incomes', 'debts', 'goals', 'expenses', 'expense_categories', 'recurring_incomes'] as $table) {
            $this->assertDatabaseCount($table, $table === 'expense_categories' ? 32 : 1);
        }
    }

    public function test_cannot_use_foreign_accounts_debts_goals_or_categories(): void
    {
        $base = [
            'description' => 'Intento',
            'amount' => 1000,
            'spent_at' => '2026-10-03',
            'budget_type' => 'need',
            'cost_type' => 'variable',
        ];

        $this->postJson('/api/expenses', $base + [
            'account_id' => $this->ids['account'],
            'expense_category_id' => $this->ids['category'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['account_id', 'expense_category_id']);

        $this->postJson('/api/incomes', [
            'account_id' => $this->ids['account'],
            'description' => 'Intento',
            'amount' => 1000,
            'received_at' => '2026-10-03',
        ])->assertUnprocessable()->assertJsonValidationErrors('account_id');

        $this->postJson('/api/expenses', $base + ['debt_id' => $this->ids['debt']])
            ->assertUnprocessable()->assertJsonValidationErrors('debt_id');

        $this->postJson('/api/expenses', $base + ['goal_id' => $this->ids['goal']])
            ->assertUnprocessable()->assertJsonValidationErrors('goal_id');

        $this->getJson("/api/transactions?account_id={$this->ids['account']}")
            ->assertUnprocessable()->assertJsonValidationErrors('account_id');
    }

    public function test_summaries_do_not_include_foreign_amounts(): void
    {
        $this->assertSame(0, (int) $this->getJson('/api/dashboard')->assertOk()->json('data.summary.accounts_count'));

        $budget = $this->getJson('/api/budgets?period=2026-10')->assertOk()->json('data');
        $this->assertNull($budget['income']['planned']);
        $this->assertEquals(0, $budget['income']['registered']);
        $this->assertEquals(0, $budget['formula']['variable_expenses']);

        $report = json_encode($this->getJson('/api/reports?date_from=2026-10-01&date_to=2026-10-31')->assertOk()->json());
        $this->assertStringNotContainsString('Cuenta del dueño', $report);
        $this->assertStringNotContainsString('Mercado', $report);
    }

    public function test_guests_cannot_access_anything(): void
    {
        auth()->forgetGuards();
        $this->app['auth']->guard('web')->logout();

        foreach (['accounts', 'incomes', 'expenses', 'debts', 'goals', 'dashboard', 'reports', 'budgets', 'transactions', 'expense-categories', 'profile'] as $resource) {
            $this->getJson("/api/{$resource}")->assertUnauthorized();
        }
    }
}
