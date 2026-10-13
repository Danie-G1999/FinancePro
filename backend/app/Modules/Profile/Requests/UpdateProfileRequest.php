<?php

namespace App\Modules\Profile\Requests;

use App\Modules\Profile\Enums\ContractType;
use App\Modules\Profile\Enums\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    private const PHONE_PATTERN = '/^\+?[0-9\s()-]{7,20}$/';

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $independent = EmploymentType::INDEPENDENT->value;
        $employee = EmploymentType::EMPLOYEE->value;

        return [
            'first_name' => ['required', 'string', 'max:100'],

            'last_name' => ['required', 'string', 'max:100'],

            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today', 'after:1900-01-01'],

            'email' => ['required', 'email', 'max:150'],

            'phone' => ['nullable', 'string', 'regex:' . self::PHONE_PATTERN],

            'address' => ['nullable', 'string', 'max:200'],

            'employment_type' => ['required', Rule::enum(EmploymentType::class)],

            'contributes_social_security' => [
                "required_if:employment_type,{$independent}",
                'nullable',
                'boolean',
            ],

            'contract_type' => [
                "required_if:employment_type,{$employee}",
                'nullable',
                Rule::enum(ContractType::class),
            ],

            'monthly_income' => [
                "required_if:employment_type,{$independent}",
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],

            'family_reference_name' => ['nullable', 'string', 'max:100'],

            'family_reference_relationship' => ['nullable', 'string', 'max:50'],

            'family_reference_phone' => ['nullable', 'string', 'regex:' . self::PHONE_PATTERN],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'El número de teléfono no es válido.',
            'family_reference_phone.regex' => 'El teléfono de la referencia no es válido.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'contributes_social_security.required_if' => 'Indica si cotizas a seguridad social.',
            'contract_type.required_if' => 'Selecciona el tipo de contrato.',
            'monthly_income.required_if' => 'Indica tus ingresos mensuales.',
        ];
    }
}
