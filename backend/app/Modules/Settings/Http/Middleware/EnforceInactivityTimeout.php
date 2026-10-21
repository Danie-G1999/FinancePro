<?php

namespace App\Modules\Settings\Http\Middleware;

use App\Modules\Settings\Services\UserSettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierre por inactividad también en el servidor: si la pestaña se cerró sin cerrar sesión,
 * el temporizador del navegador ya no existe, pero la sesión vencida igual se rechaza aquí.
 */
class EnforceInactivityTimeout
{
    public const SESSION_KEY = 'last_activity_at';

    public function __construct(
        private UserSettingsService $settings
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        // Solo sesiones de navegador (cookies); los tokens de API no tienen "inactividad".
        if (! $user || ! $request->hasSession()) {
            return $next($request);
        }

        $settings = $this->settings->forUser($user->id);
        $session = $request->session();
        $now = now()->getTimestamp();

        $last = $session->get(self::SESSION_KEY);

        if (
            $settings->auto_logout_inactive
            && $last
            && $now - $last > $settings->inactivity_timeout_minutes * 60
        ) {
            Auth::guard('web')->logout();
            $session->invalidate();
            $session->regenerateToken();

            return response()->json([
                'message' => 'Tu sesión ha expirado por inactividad.',
                'code' => 'session_inactive',
            ], 401);
        }

        $session->put(self::SESSION_KEY, $now);

        return $next($request);
    }
}
