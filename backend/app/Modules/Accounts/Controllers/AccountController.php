<?php

namespace App\Modules\Accounts\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounts\Requests\StoreAccountRequest;
use App\Modules\Accounts\Requests\UpdateAccountRequest;
use App\Modules\Accounts\Resources\AccountResource;
use App\Modules\Accounts\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $service
    ) {}

    public function index(Request $request)
    {
        $accounts = $this->service->getUserAccounts(
            $request->user()->id
        );

        return AccountResource::collection($accounts);
    }

    public function store(StoreAccountRequest $request)
    {
        $account = $this->service->createForUser(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Cuenta creada correctamente.',
            'data' => new AccountResource($account),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $account = $this->service->findOrFail($id);

        abort_unless(
            $account->user_id === $request->user()->id,
            403
        );

        return new AccountResource($account);
    }

    public function update(
        UpdateAccountRequest $request,
        int $id
    ) {
        $account = $this->service->findOrFail($id);

        abort_unless(
            $account->user_id === $request->user()->id,
            403
        );

        $account = $this->service->update(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Cuenta actualizada correctamente.',
            'data' => new AccountResource($account),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $account = $this->service->findOrFail($id);

        abort_unless(
            $account->user_id === $request->user()->id,
            403
        );

        $this->service->delete($id);

        return response()->json([
            'message' => 'Cuenta eliminada correctamente.',
        ]);
    }
}
