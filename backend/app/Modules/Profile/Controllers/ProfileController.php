<?php

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Profile\Requests\UpdatePasswordRequest;
use App\Modules\Profile\Requests\UpdateProfileRequest;
use App\Modules\Profile\Requests\UploadPhotoRequest;
use App\Modules\Profile\Resources\ProfileResource;
use App\Modules\Profile\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $service
    ) {}

    public function show(Request $request): ProfileResource
    {
        return new ProfileResource(
            $this->service->getForUser($request->user())
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $this->service->updateForUser(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'data' => new ProfileResource($profile),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $created = ! $request->hasPassword();

        $this->service->updatePassword(
            $request->user(),
            $request->validated('password'),
            $request->hasSession() ? $request->session()->getId() : null
        );

        // Nuevo identificador de sesión tras un cambio de credenciales.
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => $created
                ? 'Contraseña creada. Ahora también puedes entrar con tu correo y contraseña.'
                : 'Contraseña actualizada. Cerramos tus otras sesiones abiertas.',
        ]);
    }

    public function updatePhoto(UploadPhotoRequest $request): JsonResponse
    {
        $profile = $this->service->updatePhoto(
            $request->user(),
            $request->file('photo')
        );

        return response()->json([
            'message' => 'Foto actualizada correctamente.',
            'data' => new ProfileResource($profile),
        ]);
    }

    public function destroyPhoto(Request $request): JsonResponse
    {
        $profile = $this->service->removePhoto($request->user());

        return response()->json([
            'message' => 'Foto eliminada correctamente.',
            'data' => new ProfileResource($profile),
        ]);
    }
}
