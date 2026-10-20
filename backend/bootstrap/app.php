<?php

use App\Modules\Income\Http\Middleware\GenerateRecurringIncomes;
use App\Modules\Legal\Http\Middleware\EnsureLegalAccepted;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // En Render un proxy termina el HTTPS: sin esto Laravel vería "http" (cookies
        // seguras y URL de Google mal) y la IP del proxy en vez de la del usuario
        // (el límite de intentos bloquearía a todos a la vez).
        $middleware->trustProxies(at: '*');

        $middleware->statefulApi();

        // Primero la autorización de datos: sin ella no se procesa nada del usuario.
        $middleware->api(append: [EnsureLegalAccepted::class, GenerateRecurringIncomes::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
