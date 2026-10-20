<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\DTO\RegisterDTO;
use App\Modules\Auth\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected AuthRepositoryInterface $repository
    ) {
    }

    public function register(RegisterDTO $dto): User
    {
        return $this->repository->create(
            $dto->toArray()
        );
    }

    public function login(LoginDTO $dto): User
    {
        // Cuentas creadas con Google no tienen contraseña: se les indica cómo entrar.
        $googleOnly = User::query()
            ->where('email', $dto->email)
            ->whereNull('password')
            ->whereNotNull('google_id')
            ->exists();

        if ($googleOnly) {
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta se creó con Google. Usa "Continuar con Google" para entrar.'],
            ]);
        }

        if (!Auth::attempt(
            $dto->credentials(),
            $dto->remember
        )) {
            throw ValidationException::withMessages([
                'email' => [
                    'Las credenciales proporcionadas no son correctas.'
                ],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }

    public function user(): ?User
    {
        return Auth::guard('web')->user();
    }
}
