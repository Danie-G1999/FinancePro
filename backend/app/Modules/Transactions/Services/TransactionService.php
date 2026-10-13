<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Interfaces\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionService
{
    public const PER_PAGE = 20;

    public function __construct(
        private TransactionRepositoryInterface $repository
    ) {}

    public function getUserTransactions(int $userId, array $filters): LengthAwarePaginator
    {
        $filters['search'] = isset($filters['search'])
            ? trim($filters['search'])
            : null;

        return $this->repository->paginateByUser($userId, $filters, self::PER_PAGE);
    }
}
