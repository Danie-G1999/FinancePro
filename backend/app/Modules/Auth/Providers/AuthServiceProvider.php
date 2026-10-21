<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Interfaces\AuthRepositoryInterface;
use App\Modules\Auth\Repositories\AuthRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public const LOGIN_ATTEMPTS_PER_MINUTE = 5;

    public const REGISTER_ATTEMPTS_PER_HOUR = 10;

    public function register(): void
    {
        $this->app->bind(
            AuthRepositoryInterface::class,
            AuthRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Por correo + IP frena la adivinación de contraseñas de una cuenta;
        // el límite por IP frena a quien prueba muchos correos distintos.
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(self::LOGIN_ATTEMPTS_PER_MINUTE)
                ->by(mb_strtolower((string) $request->input('email')) . '|' . $request->ip())
                ->response(fn (Request $request, array $headers) => $this->tooManyAttempts($headers)),

            Limit::perMinute(self::LOGIN_ATTEMPTS_PER_MINUTE * 4)
                ->by('ip:' . $request->ip())
                ->response(fn (Request $request, array $headers) => $this->tooManyAttempts($headers)),
        ]);

        // Ida y vuelta a Google: holgado para uso normal, frena abusos del callback.
        RateLimiter::for('oauth', fn (Request $request) => Limit::perMinute(self::LOGIN_ATTEMPTS_PER_MINUTE * 4)
            ->by('oauth:' . $request->ip()));

        // Cambio de contraseña: frena a quien intente adivinar la actual con una sesión ajena.
        RateLimiter::for('password-change', fn (Request $request) => Limit::perMinute(self::LOGIN_ATTEMPTS_PER_MINUTE)
            ->by('password:' . ($request->user()?->getAuthIdentifier() ?? $request->ip()))
            ->response(fn (Request $request, array $headers) => $this->tooManyAttempts($headers)));

        RateLimiter::for('register', fn (Request $request) => Limit::perHour(self::REGISTER_ATTEMPTS_PER_HOUR)
            ->by($request->ip())
            ->response(fn (Request $request, array $headers) => $this->tooManyAttempts($headers)));
    }

    private function tooManyAttempts(array $headers)
    {
        $seconds = (int) ($headers['Retry-After'] ?? 60);

        $wait = $seconds >= 120
            ? ceil($seconds / 60) . ' minutos'
            : "{$seconds} segundos";

        return response()->json([
            'message' => "Demasiados intentos. Espera {$wait} e inténtalo de nuevo.",
            'retry_after' => $seconds,
        ], 429, $headers);
    }
}
