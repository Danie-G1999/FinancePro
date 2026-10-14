<?php

namespace App\Modules\Expenses\Requests;

use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Budgets\Enums\CostType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where('is_active', true),
            ],

            'description' => ['required', 'string', 'max:150'],

            'debt_id' => [
                'nullable',
                'integer',
                'prohibits:goal_id',
                Rule::exists('debts', 'id')
                    ->where('user_id', $this->user()->id),
            ],

            'goal_id' => [
                'nullable',
                'integer',
                Rule::exists('goals', 'id')
                    ->where('user_id', $this->user()->id),
            ],

            // Los pagos de deuda y aportes a metas se clasifican solos.
            'budget_type' => [
                'nullable',
                'required_without_all:debt_id,goal_id',
                Rule::in([BudgetType::NEED->value, BudgetType::WANT->value]),
            ],

            'cost_type' => [
                'nullable',
                'required_without_all:debt_id,goal_id',
                Rule::enum(CostType::class),
            ],

            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],

            'spent_at' => ['required', 'date_format:Y-m-d'],

            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.exists' => 'La cuenta seleccionada no es válida.',
            'debt_id.exists' => 'La deuda seleccionada no es válida.',
            'debt_id.prohibits' => 'Un gasto no puede ser pago de deuda y aporte a meta a la vez.',
            'goal_id.exists' => 'La meta seleccionada no es válida.',
            'budget_type.required_without_all' => 'Selecciona la categoría del gasto.',
            'cost_type.required_without_all' => 'Indica si el gasto es fijo o variable.',
        ];
    }
}
