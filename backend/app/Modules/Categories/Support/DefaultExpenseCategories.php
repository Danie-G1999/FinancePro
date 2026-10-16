<?php

namespace App\Modules\Categories\Support;

use App\Modules\Budgets\Enums\BudgetType;

/** Categorías con las que arranca cada usuario; luego puede editarlas o desactivarlas. */
final class DefaultExpenseCategories
{
    public const COLORS = [
        'need' => '#2563eb',
        'want' => '#d97706',
        'savings' => '#16a34a',
    ];

    /** @return array<string, list<array{name: string, icon: string}>> */
    public static function all(): array
    {
        return [
            BudgetType::NEED->value => [
                ['name' => 'Mercado', 'icon' => '🛒'],
                ['name' => 'Arriendo/Hipoteca', 'icon' => '🏠'],
                ['name' => 'Servicios públicos', 'icon' => '💡'],
                ['name' => 'Transporte', 'icon' => '🚌'],
                ['name' => 'Salud/Medicina', 'icon' => '💊'],
                ['name' => 'Educación', 'icon' => '🎓'],
            ],
            BudgetType::WANT->value => [
                ['name' => 'Restaurantes', 'icon' => '🍽️'],
                ['name' => 'Entretenimiento', 'icon' => '🎬'],
                ['name' => 'Viajes', 'icon' => '✈️'],
                ['name' => 'Suscripciones', 'icon' => '📺'],
                ['name' => 'Ropa y Calzado', 'icon' => '👕'],
                ['name' => 'Cuidado personal', 'icon' => '💇'],
            ],
            BudgetType::SAVINGS->value => [
                ['name' => 'Fondo de emergencia', 'icon' => '🛟'],
                ['name' => 'Inversiones', 'icon' => '📈'],
                ['name' => 'Pago voluntario de deuda', 'icon' => '💳'],
                ['name' => 'Metas de ahorro', 'icon' => '🎯'],
            ],
        ];
    }
}
