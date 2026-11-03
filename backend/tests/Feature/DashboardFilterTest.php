<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Verifica los filtros de mes y cuenta del dashboard. */
class DashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    private int $main;

    private int $savings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());

        $this->main = $this->createAccount('Principal');
        $this->savings = $this->createAccount('Ahorros');

        $this->createIncome($this->main, 'Salario septiembre', 100000, '2026-09-10');
        $this->createIncome($this->main, 'Salario octubre', 200000, '2026-10-10');
        $this->createIncome($this->savings, 'Intereses octubre', 50000, '2026-10-15');
    }

    public function test_period_selects_the_month_shown_in_the_summary(): void
    {
        $data = $this->getJson('/api/dashboard?period=2026-09')->assertOk()->json('data');

        $this->assertSame('2026-09', $data['filters']['period']);
        $this->assertEquals(100000, $data['summary']['month_income']);
        $this->assertSame('2026-09', end($data['cash_flow'])['month']);
        $this->assertSame(['Salario septiembre'], array_column($data['recent_movements'], 'description'));
    }

    public function test_account_filter_limits_amounts_and_movements(): void
    {
        $data = $this->getJson("/api/dashboard?period=2026-10&account_id={$this->savings}")
            ->assertOk()
            ->json('data');

        $this->assertSame($this->savings, $data['filters']['account_id']);
        $this->assertEquals(50000, $data['summary']['month_income']);
        $this->assertSame(1, $data['summary']['accounts_count']);
        $this->assertSame(['Intereses octubre'], array_column($data['recent_movements'], 'description'));
        $this->assertCount(2, $data['accounts']);
    }

    public function test_rejects_accounts_of_other_users_and_invalid_periods(): void
    {
        $foreign = User::factory()->create();
        $this->actingAs($foreign);
        $foreignAccount = $this->createAccount('Ajena');

        $this->actingAs(User::query()->whereKeyNot($foreign->id)->first());

        $this->getJson("/api/dashboard?account_id={$foreignAccount}")->assertUnprocessable();
        $this->getJson('/api/dashboard?period=2026-13')->assertUnprocessable();
    }

    private function createAccount(string $name): int
    {
        return $this->postJson('/api/accounts', [
            'name' => $name,
            'type' => 'savings',
            'initial_balance' => 0,
            'currency' => 'COP',
            'is_active' => true,
        ])->assertCreated()->json('data.id');
    }

    private function createIncome(int $accountId, string $description, int $amount, string $date): void
    {
        $this->postJson('/api/incomes', [
            'account_id' => $accountId,
            'description' => $description,
            'amount' => $amount,
            'received_at' => $date,
        ])->assertCreated();
    }
}
