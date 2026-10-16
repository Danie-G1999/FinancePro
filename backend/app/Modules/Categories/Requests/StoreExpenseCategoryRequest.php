<?php

namespace App\Modules\Categories\Requests;

use App\Modules\Budgets\Enums\BudgetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->name)) {
            $this->merge(['name' => trim($this->name)]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:60',
                Rule::unique('expense_categories', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($this->route('id')),
            ],

            'budget_type' => ['required', Rule::enum(BudgetType::class)],

            'icon' => ['nullable', 'string', 'max:16'],

            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.unique' => 'Ya tienes una categoría con ese nombre.',
            'budget_type.required' => 'Selecciona la categoría del presupuesto.',
            'budget_type.enum' => 'La categoría del presupuesto no es válida.',
            'color.regex' => 'El color debe tener el formato #RRGGBB.',
        ];
    }
}
