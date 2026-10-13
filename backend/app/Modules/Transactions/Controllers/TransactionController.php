<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Requests\ListTransactionsRequest;
use App\Modules\Transactions\Resources\TransactionResource;
use App\Modules\Transactions\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $service
    ) {}

    public function index(ListTransactionsRequest $request)
    {
        $transactions = $this->service->getUserTransactions(
            $request->user()->id,
            $request->safe()->only(['date_from', 'date_to', 'account_id', 'search'])
        );

        return TransactionResource::collection($transactions);
    }
}
