<?php

namespace App\Modules\Income\Http\Middleware;

use App\Modules\Income\Services\RecurringIncomeService;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** Registra los ingresos recurrentes vencidos una vez al día por usuario, sin depender del cron. */
class GenerateRecurringIncomes
{
    public function __construct(
        private RecurringIncomeService $service
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        if ($user) {
            $today = CarbonImmutable::today();
            $key = "recurring-incomes:{$user->id}:{$today->toDateString()}";

            if (Cache::add($key, true, $today->endOfDay())) {
                try {
                    $this->service->generateDue($user->id, $today);
                } catch (Throwable $exception) {
                    Cache::forget($key);

                    report($exception);
                }
            }
        }

        return $next($request);
    }
}
