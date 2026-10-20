<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

class GoogleAuthController
{
    public function __construct(
        protected GoogleAuthService $google
    ) {
    }

    /** El frontend solo muestra "Continuar con Google" si está configurado. */
    public function providers(): JsonResponse
    {
        return response()->json([
            'data' => ['google' => $this->google->isConfigured()],
        ]);
    }

    public function redirect(): SymfonyRedirect
    {
        if (! $this->google->isConfigured()) {
            return $this->backToLogin('google_unavailable');
        }

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        // El usuario canceló en la pantalla de Google.
        if ($request->filled('error')) {
            return $this->backToLogin('google_cancelled');
        }

        try {
            $user = $this->google->resolveUser(
                Socialite::driver('google')->user()
            );
        } catch (RuntimeException $exception) {
            return $this->backToLogin($exception->getMessage() === 'google_email_unverified'
                ? 'google_email_unverified'
                : 'google_failed');
        } catch (Throwable $exception) {
            Log::warning('Fallo el inicio de sesión con Google.', ['error' => $exception->getMessage()]);

            return $this->backToLogin('google_failed');
        }

        Auth::guard('web')->login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->away(config('services.frontend_url') . '/dashboard');
    }

    private function backToLogin(string $error): RedirectResponse
    {
        return redirect()->away(config('services.frontend_url') . '/login?error=' . $error);
    }
}
