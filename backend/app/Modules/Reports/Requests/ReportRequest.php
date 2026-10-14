<?php

namespace App\Modules\Reports\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public const MAX_RANGE_DAYS = 731;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date_format:Y-m-d'],

            'date_to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
                fn (string $attribute, mixed $value, \Closure $fail) => $this->validateRange($value, $fail),
            ],

            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }

    private function validateRange(mixed $to, \Closure $fail): void
    {
        $from = $this->input('date_from');

        if (! $from || ! is_string($to)) {
            return;
        }

        $days = CarbonImmutable::parse($from)->diffInDays(CarbonImmutable::parse($to));

        if ($days > self::MAX_RANGE_DAYS) {
            $fail('El rango del reporte no puede superar 2 años.');
        }
    }
}
