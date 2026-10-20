<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;

class GoogleAuthService
{
    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    /**
     * Devuelve el usuario de esa cuenta de Google, creándolo si es nuevo.
     * Si ya existe una cuenta con ese correo, la vincula en lugar de duplicarla.
     */
    public function resolveUser(SocialiteUser $googleUser): User
    {
        $email = mb_strtolower(trim((string) $googleUser->getEmail()));

        // Solo se confía en el correo si Google lo verificó; si no, cualquiera
        // podría vincularse a una cuenta ajena creando un Google con ese correo.
        if ($email === '' || ! ($googleUser->getRaw()['email_verified'] ?? false)) {
            throw new RuntimeException('google_email_unverified');
        }

        return DB::transaction(function () use ($googleUser, $email) {
            $user = User::query()->where('google_id', $googleUser->getId())->first()
                ?? User::query()->where('email', $email)->first();

            if ($user) {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                return $user;
            }

            $user = new User();

            $user->forceFill([
                'name' => $googleUser->getName() ?: strstr($email, '@', true),
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => null,
                'email_verified_at' => now(),
            ])->save();

            return $user;
        });
    }
}
