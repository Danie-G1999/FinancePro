<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\UpdateUserSettingsRequest;
use App\Modules\Settings\Resources\UserSettingsResource;
use App\Modules\Settings\Services\UserSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function __construct(
        private UserSettingsService $settings
    ) {}

    // 200 aunque la fila se haya creado en esta consulta (es una lectura, no un alta).
    public function show(Request $request): JsonResponse
    {
        return (new UserSettingsResource(
            $this->settings->forUser($request->user()->id)
        ))->response()->setStatusCode(200);
    }

    /** La renovación la hace EnforceInactivityTimeout; aquí no hay nada más que hacer. */
    public function heartbeat(): JsonResponse
    {
        return response()->json(null, 204);
    }

    public function update(UpdateUserSettingsRequest $request): JsonResponse
    {
        $settings = $this->settings->update($request->user()->id, $request->settings());

        return response()->json([
            'message' => 'Configuración guardada correctamente.',
            'data' => new UserSettingsResource($settings),
        ]);
    }
}
