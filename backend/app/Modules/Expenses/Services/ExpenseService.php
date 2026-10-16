<?php

namespace App\Modules\Expenses\Services;

use App\Modules\Accounts\Enums\AccountType;
use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Budgets\Enums\CostType;
use App\Modules\Categories\Interfaces\ExpenseCategoryRepositoryInterface;
use App\Modules\Debts\Interfaces\DebtRepositoryInterface;
use App\Modules\Expenses\Interfaces\ExpenseRepositoryInterface;
use App\Modules\Goals\Interfaces\GoalRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService extends BaseService
{
    public const PER_PAGE = 20;

    public function __construct(
        ExpenseRepositoryInterface $repository,
        private AccountRepositoryInterface $accounts,
        private DebtRepositoryInterface $debts,
        private GoalRepositoryInterface $goals,
        private ExpenseCategoryRepositoryInterface $categories
    ) {
        parent::__construct($repository);
    }

    public function getUserExpenses(int $userId): LengthAwarePaginator
    {
        return $this->repository->paginateByUser($userId, self::PER_PAGE);
    }

    public function createForUser(int $userId, array $data): Model
    {
        return DB::transaction(function () use ($userId, $data) {
            $data = $this->classify($data);

            $this->validateMovement($userId, $data);

            $data['user_id'] = $userId;

            return $this->repository->create($data);
        });
    }

    public function findForUser(int $userId, int $id): Model
    {
        return $this->repository->findByUserOrFail($userId, $id);
    }

    public function updateForUser(int $userId, int $id, array $data): Model
    {
        return DB::transaction(function () use ($userId, $id, $data) {
            $expense = $this->findForUser($userId, $id);

            $data = $this->classify($data);

            $this->validateMovement($userId, $data, $expense);

            return $this->repository->update($expense->getKey(), $data);
        });
    }

    public function deleteForUser(int $userId, int $id): bool
    {
        $expense = $this->findForUser($userId, $id);

        return $this->repository->delete($expense->getKey());
    }

    private function validateMovement(
        int $userId,
        array $data,
        ?Model $current = null
    ): void {
        $account = $this->accounts->findByUserOrFail(
            $userId,
            (int) $data['account_id']
        );

        $this->ensureSufficientFunds($account, $data, $current);

        if ($data['debt_id']) {
            $this->ensureValidDebtPayment($userId, $account, $data, $current);
        }

        if ($data['goal_id']) {
            $this->ensureValidGoalContribution($userId, $account, $data, $current);
        }

        if ($data['expense_category_id']) {
            $this->ensureValidCategory($userId, $data, $current);
        }
    }

    /** Pagos de deuda y aportes a metas van al bloque de ahorro y deudas; el resto lo elige el usuario. */
    private function classify(array $data): array
    {
        $data['debt_id'] ??= null;
        $data['goal_id'] ??= null;
        $data['expense_category_id'] ??= null;

        if ($data['debt_id']) {
            $data['budget_type'] = BudgetType::SAVINGS->value;
            $data['cost_type'] = CostType::FIXED->value;
            $data['expense_category_id'] = null;
        } elseif ($data['goal_id']) {
            $data['budget_type'] = BudgetType::SAVINGS->value;
            $data['cost_type'] = null;
            $data['expense_category_id'] = null;
        }

        return $data;
    }

    /** Las tarjetas de crédito pueden gastar por encima de su saldo. */
    private function ensureSufficientFunds(
        Model $account,
        array $data,
        ?Model $current
    ): void {
        if ($account->type === AccountType::CREDIT_CARD) {
            return;
        }

        $available = $account->currentBalance();

        // Al editar, el gasto actual ya está descontado del saldo.
        if ($current && $current->account_id === $account->getKey()) {
            $available += (float) $current->amount;
        }

        if ((float) $data['amount'] > $available) {
            throw ValidationException::withMessages([
                'amount' => 'El valor supera el saldo disponible de la cuenta.',
            ]);
        }
    }

    private function ensureValidDebtPayment(
        int $userId,
        Model $account,
        array $data,
        ?Model $current
    ): void {
        $debt = $this->debts->findByUserOrFail($userId, (int) $data['debt_id']);

        if ($debt->currency !== $account->currency) {
            throw ValidationException::withMessages([
                'account_id' => "La cuenta debe estar en {$debt->currency}, la moneda de la deuda.",
            ]);
        }

        $remaining = $debt->remainingBalance();

        // Al editar, el pago actual ya está descontado de la deuda.
        if ($current && $current->debt_id === $debt->getKey()) {
            $remaining += (float) $current->amount;
        }

        if ($remaining <= 0) {
            throw ValidationException::withMessages([
                'debt_id' => 'Esta deuda ya está pagada por completo.',
            ]);
        }

        if ((float) $data['amount'] > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'El valor supera el saldo pendiente de la deuda.',
            ]);
        }
    }

    /** La categoría debe ser del bloque elegido y estar activa, salvo que el gasto ya la tuviera. */
    private function ensureValidCategory(
        int $userId,
        array $data,
        ?Model $current
    ): void {
        $category = $this->categories->findByUserOrFail(
            $userId,
            (int) $data['expense_category_id']
        );

        if ($category->budget_type->value !== $data['budget_type']) {
            throw ValidationException::withMessages([
                'expense_category_id' => "La categoría {$category->name} no pertenece a la categoría del presupuesto seleccionada.",
            ]);
        }

        $keepsCurrent = $current && $current->expense_category_id === $category->getKey();

        if (! $category->is_active && ! $keepsCurrent) {
            throw ValidationException::withMessages([
                'expense_category_id' => "La categoría {$category->name} está desactivada.",
            ]);
        }
    }

    private function ensureValidGoalContribution(
        int $userId,
        Model $account,
        array $data,
        ?Model $current
    ): void {
        $goal = $this->goals->findByUserOrFail($userId, (int) $data['goal_id']);

        if ($goal->currency !== $account->currency) {
            throw ValidationException::withMessages([
                'account_id' => "La cuenta debe estar en {$goal->currency}, la moneda de la meta.",
            ]);
        }

        $remaining = $goal->remainingAmount();

        // Al editar, el aporte actual ya está sumado a la meta.
        if ($current && $current->goal_id === $goal->getKey()) {
            $remaining += (float) $current->amount;
        }

        if ($remaining <= 0) {
            throw ValidationException::withMessages([
                'goal_id' => 'Esta meta ya alcanzó su valor objetivo.',
            ]);
        }

        if ((float) $data['amount'] > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'El valor supera lo que falta para completar la meta.',
            ]);
        }
    }
}
