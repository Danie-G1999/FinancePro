<?php

namespace App\Modules\Legal\Http\Middleware;

use App\Modules\Legal\Services\LegalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sin autorización vigente no se tratan los datos del usuario (Ley 1581 de 2012, art. 9):
 * solo quedan disponibles la autenticación y la aceptación de los documentos.
 */
class EnsureLegalAccepted
{
    private const ALLOWED = ['api/auth/*', 'api/legal/*', 'api/user', 'api/user/session/*'];

    public function __construct(
        private LegalService $legal
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        if (! $user || $request->is(...self::ALLOWED)) {
            return $next($request);
        }

        if ($this->legal->pendingFor($user) !== []) {
            return response()->json([
                'message' => 'Debes aceptar los Términos y Condiciones y la Política de Tratamiento de Datos para continuar.',
                'code' => 'legal_acceptance_required',
            ], 403);
        }

        return $next($request);
    }
}
