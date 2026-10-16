<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Accounts\Models\Account;
use App\Modules\Categories\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_new_users_get_the_default_categories(): void
    {
        $this->assertSame(16, ExpenseCategory::where('user_id', $this->user->id)->count());
        $this->assertSame(6, ExpenseCategory::where('user_id', $this->user->id)->where('budget_type', 'need')->count());
    }

    public function test_it_filters_by_budget_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/expense-categories?budget_type=want&active=1')
            ->assertOk();

        $this->assertCount(6, $response->json('data'));
        $this->assertSame(['want'], array_values(array_unique(array_column($response->json('data'), 'budget_type'))));
    }

    public function test_it_creates_updates_and_deletes_a_category(): void
    {
        $id = $this->actingAs($this->user)
            ->postJson('/api/expense-categories', ['name' => 'Mascotas', 'budget_type' => 'need'])
            ->assertCreated()
            ->json('data.id');

        $this->putJson("/api/expense-categories/{$id}", ['name' => 'Mascotas', 'budget_type' => 'want', 'is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.budget_type', 'want')
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/expense-categories/{$id}")
            ->assertOk()
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('expense_categories', ['id' => $id]);
    }

    public function test_it_rejects_duplicates_and_missing_budget_type(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/expense-categories', ['name' => 'Mercado'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'budget_type']);
    }

    public function test_users_cannot_touch_other_users_categories(): void
    {
        $foreign = ExpenseCategory::where('user_id', User::factory()->create()->id)->first();

        $this->actingAs($this->user)
            ->deleteJson("/api/expense-categories/{$foreign->id}")
            ->assertNotFound();
    }

    public function test_expenses_require_a_category_of_the_selected_budget_type(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'savings',
            'initial_balance' => 1000000,
        ]);

        $category = fn (string $name) => ExpenseCategory::where('user_id', $this->user->id)->where('name', $name)->value('id');

        $payload = [
            'account_id' => $account->id,
            'description' => 'Cena',
            'amount' => 50000,
            'spent_at' => '2026-10-16',
            'budget_type' => 'want',
            'cost_type' => 'variable',
        ];

        $this->actingAs($this->user)
            ->postJson('/api/expenses', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('expense_category_id');

        $this->postJson('/api/expenses', $payload + ['expense_category_id' => $category('Mercado')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('expense_category_id');

        $this->postJson('/api/expenses', $payload + ['expense_category_id' => $category('Restaurantes')])
            ->assertCreated()
            ->assertJsonPath('data.expense_category_name', 'Restaurantes');
    }

    public function test_categories_in_use_are_deactivated_instead_of_deleted(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'savings',
            'initial_balance' => 1000000,
        ]);

        $categoryId = ExpenseCategory::where('user_id', $this->user->id)->where('name', 'Mercado')->value('id');

        $this->actingAs($this->user)->postJson('/api/expenses', [
            'account_id' => $account->id,
            'description' => 'Mercado semanal',
            'amount' => 120000,
            'spent_at' => '2026-10-16',
            'budget_type' => 'need',
            'cost_type' => 'variable',
            'expense_category_id' => $categoryId,
        ])->assertCreated();

        $this->deleteJson("/api/expense-categories/{$categoryId}")
            ->assertOk()
            ->assertJsonPath('deleted', false);

        $this->assertDatabaseHas('expense_categories', ['id' => $categoryId, 'is_active' => false]);
    }
}
