<?php

namespace App\Modules\Settings\Requests;

use App\Modules\Settings\Enums\BudgetMethod;
use App\Modules\Settings\Support\Currencies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserSettingsRequest extends FormRequest
{
    public const TIMEOUT_OPTIONS = [5, 10, 15, 30];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $custom = 'required_if:budget_method,' . BudgetMethod::CUSTOM->value;

        return [
            'currency' => ['required', 'string', Rule::in(Currencies::codes())],

            'budget_method' => ['required', Rule::enum(BudgetMethod::class)],

            'budget_need_percent' => [$custom, 'nullable', 'integer', 'between:0,100'],
            'budget_want_percent' => [$custom, 'nullable', 'integer', 'between:0,100'],
            'budget_savings_percent' => [$custom, 'nullable', 'integer', 'between:0,100'],

            'auto_logout_inactive' => ['required', 'boolean'],

            'inactivity_timeout_minutes' => ['required', 'integer', Rule::in(self::TIMEOUT_OPTIONS)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('budget_method') !== BudgetMethod::CUSTOM->value || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $total = (int) $this->input('budget_need_percent')
                    + (int) $this->input('budget_want_percent')
                    + (int) $this->input('budget_savings_percent');

                if ($total !== 100) {
                    $validator->errors()->add(
                        'budget_need_percent',
                        "Los porcentajes deben sumar 100% (ahora suman {$total}%)."
                    );
                }
            },
        ];
    }

    /** Con 50/30/20 los porcentajes personalizados no se tocan. */
    public function settings(): array
    {
        $data = $this->validated();

        if ($data['budget_method'] !== BudgetMethod::CUSTOM->value) {
            unset($data['budget_need_percent'], $data['budget_want_percent'], $data['budget_savings_percent']);
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'currency.in' => 'Selecciona una moneda de la lista.',
            'budget_method.enum' => 'Selecciona un método de presupuesto válido.',
            'budget_need_percent.required_if' => 'Indica el porcentaje para necesidades.',
            'budget_want_percent.required_if' => 'Indica el porcentaje para deseos.',
            'budget_savings_percent.required_if' => 'Indica el porcentaje para ahorro.',
            '*.between' => 'Cada porcentaje debe estar entre 0 y 100.',
            'inactivity_timeout_minutes.in' => 'Selecciona un tiempo de inactividad válido.',
        ];
    }
}
