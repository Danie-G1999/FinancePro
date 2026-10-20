<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\DTO\RegisterDTO;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Legal\Services\LegalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController
{
    public function __construct(
        protected AuthService $authService,
        protected LegalService $legal
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterDTO(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        $user = $this->authService->register($dto);

        $this->legal->accept($user, ['terms', 'privacy'], $request);

        // Queda con la sesión iniciada para entrar directo a su panel.
        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request): UserResource
    {
        $dto = new LoginDTO(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember'),
        );

        $user = $this->authService->login($dto);

        $request->session()->regenerate();

        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /** Marca el recorrido guiado como visto (terminado o saltado); solo cuenta la primera vez. */
    public function completeOnboarding(Request $request): UserResource
    {
        $user = $request->user();

        if ($user->onboarding_completed_at === null) {
            $user->forceFill(['onboarding_completed_at' => now()])->save();
        }

        return new UserResource($user);
    }

    public function me(): UserResource
    {
        return new UserResource(
            $this->authService->user()
        );
    }
}
