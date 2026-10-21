<?php

namespace App\Modules\Profile\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /** Las cuentas creadas con Google no tienen contraseña: la crean sin pedir la actual. */
    public function hasPassword(): bool
    {
        return $this->user()->password !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => $this->hasPassword()
                ? ['required', 'string', 'current_password:web']
                : ['nullable'],

            // Mismas reglas que el registro.
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->letters()->numbers(),
                ...($this->hasPassword() ? ['different:current_password'] : []),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Escribe tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Escribe la nueva contraseña.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
            'password.different' => 'La nueva contraseña debe ser distinta de la actual.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
        ];
    }
}
