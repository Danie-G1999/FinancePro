<?php

namespace App\Modules\Transactions\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date_format:Y-m-d'],

            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],

            'account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()->id),
            ],

            'search' => ['nullable', 'string', 'max:100'],

            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            'account_id.exists' => 'La cuenta seleccionada no es válida.',
        ];
    }
}
