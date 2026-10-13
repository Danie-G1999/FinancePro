<?php

namespace App\Modules\Debts\Requests;

use App\Modules\Debts\Enums\DebtType;
use App\Modules\Debts\Enums\PaymentFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],

            'type' => ['required', Rule::enum(DebtType::class)],

            'creditor' => ['nullable', 'string', 'max:150'],

            'total_amount' => ['required', 'numeric', 'gt:0'],

            'installments' => ['required', 'integer', 'min:1', 'max:600'],

            'payment_frequency' => ['required', Rule::enum(PaymentFrequency::class)],

            'payment_day' => ['required', 'integer', 'between:1,31'],

            'second_payment_day' => [
                'required_if:payment_frequency,biweekly',
                'nullable',
                'integer',
                'between:1,31',
                'different:payment_day',
            ],

            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:999.99'],

            'monthly_payment' => ['nullable', 'numeric', 'min:0'],

            'currency' => ['required', 'string', 'size:3'],

            'due_date' => ['nullable', 'date'],

            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'installments.min' => 'La deuda debe tener al menos 1 cuota.',
            'payment_day.between' => 'El día de pago debe estar entre 1 y 31.',
            'second_payment_day.required_if' => 'Indica el segundo día de pago de la quincena.',
            'second_payment_day.between' => 'El segundo día de pago debe estar entre 1 y 31.',
            'second_payment_day.different' => 'Los dos días de pago deben ser distintos.',
        ];
    }
}
