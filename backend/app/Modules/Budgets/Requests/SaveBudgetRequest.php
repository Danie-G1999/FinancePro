<?php

namespace App\Modules\Budgets\Requests;

class SaveBudgetRequest extends BudgetPeriodRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),

            'net_income' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],

            'currency' => ['required', 'string', 'size:3'],
        ];
    }
}
