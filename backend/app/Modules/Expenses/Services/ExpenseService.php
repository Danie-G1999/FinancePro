<?php

namespace App\Modules\Expenses\Services;

use App\Modules\Accounts\Enums\AccountType;
use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Modules\Debts\Interfaces\DebtRepositoryInterface;
use App\Modules\Expenses\Interfaces\ExpenseRepositoryInterface;
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
        private DebtRepositoryInterface $debts
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
            $data['debt_id'] ??= null;

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

            $data['debt_id'] ??= null;

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
}
