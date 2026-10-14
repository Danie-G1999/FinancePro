<?php

namespace App\Modules\Income\Services;

use App\Modules\Income\Interfaces\IncomeRepositoryInterface;
use App\Modules\Income\Interfaces\RecurringIncomeRepositoryInterface;
use App\Modules\Income\Models\RecurringIncome;
use App\Shared\Services\BaseService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class RecurringIncomeService extends BaseService
{
    public function __construct(
        RecurringIncomeRepositoryInterface $repository,
        private IncomeRepositoryInterface $incomes
    ) {
        parent::__construct($repository);
    }

    public function getUserSchedules(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function createForUser(int $userId, array $data): Model
    {
        $data['user_id'] = $userId;

        $created = $this->repository->create($data);

        // Se relee para contar con los valores por defecto de la base de datos.
        $schedule = $this->repository->findByUserOrFail($userId, $created->getKey());

        $this->generateFor($schedule, CarbonImmutable::today());

        return $this->repository->findByUserOrFail($userId, $schedule->getKey());
    }

    public function findForUser(int $userId, int $id): Model
    {
        return $this->repository->findByUserOrFail($userId, $id);
    }

    public function updateForUser(int $userId, int $id, array $data): Model
    {
        $schedule = $this->findForUser($userId, $id);

        $updated = $this->repository->update($schedule->getKey(), $data);

        $this->generateFor($updated, CarbonImmutable::today());

        return $this->repository->findByUserOrFail($userId, $id);
    }

    public function setActive(int $userId, int $id, bool $active): Model
    {
        $schedule = $this->findForUser($userId, $id);

        $updated = $this->repository->update($schedule->getKey(), ['is_active' => $active]);

        $this->generateFor($updated, CarbonImmutable::today());

        return $this->repository->findByUserOrFail($userId, $id);
    }

    /** Los ingresos ya registrados se conservan; solo se detienen los próximos. */
    public function deleteForUser(int $userId, int $id): bool
    {
        $schedule = $this->findForUser($userId, $id);

        return $this->repository->delete($schedule->getKey());
    }

    /** Registra los ingresos recurrentes que ya llegaron. Devuelve cuántos creó. */
    public function generateDue(?int $userId = null, ?CarbonImmutable $today = null): int
    {
        $today ??= CarbonImmutable::today();

        return $this->repository
            ->getActive($userId)
            ->sum(fn (RecurringIncome $schedule) => $this->generateFor($schedule, $today));
    }

    private function generateFor(RecurringIncome $schedule, CarbonImmutable $today): int
    {
        // Las cuentas inactivas no reciben ingresos nuevos.
        if (! $schedule->is_active || ! $schedule->account?->is_active) {
            return 0;
        }

        $created = 0;
        $lastPeriod = $schedule->last_generated_period;
        $startDate = CarbonImmutable::parse($schedule->start_date);

        $month = $lastPeriod
            ? CarbonImmutable::createFromFormat('!Y-m', $lastPeriod)->addMonth()
            : $startDate->startOfMonth();

        DB::transaction(function () use ($schedule, $today, $startDate, &$month, &$created, &$lastPeriod) {
            while ($month <= $today->startOfMonth()) {
                $date = $schedule->paymentDateFor($month);

                if ($date > $today) {
                    break;
                }

                if ($date >= $startDate && $this->registerIncome($schedule, $date)) {
                    $created++;
                }

                $lastPeriod = $month->format('Y-m');
                $month = $month->addMonth();
            }

            if ($lastPeriod !== $schedule->last_generated_period) {
                $this->repository->update($schedule->getKey(), [
                    'last_generated_period' => $lastPeriod,
                ]);
            }
        });

        return $created;
    }

    private function registerIncome(RecurringIncome $schedule, CarbonImmutable $date): bool
    {
        try {
            $this->incomes->create([
                'user_id' => $schedule->user_id,
                'account_id' => $schedule->account_id,
                'recurring_income_id' => $schedule->getKey(),
                'description' => $schedule->description,
                'amount' => $schedule->amount,
                'received_at' => $date->toDateString(),
                'notes' => $schedule->notes,
            ]);

            return true;
        } catch (UniqueConstraintViolationException) {
            // Otra petición ya lo registró.
            return false;
        }
    }
}
