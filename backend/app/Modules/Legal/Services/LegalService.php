<?php

namespace App\Modules\Legal\Services;

use App\Models\User;
use App\Modules\Legal\Models\LegalAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegalService
{
    /** @return array<string, string> documento => versión vigente */
    public function currentVersions(): array
    {
        return collect(config('legal.documents'))
            ->map(fn (array $document) => $document['version'])
            ->all();
    }

    /** Documentos cuya versión vigente el usuario todavía no ha aceptado. */
    public function pendingFor(User $user): array
    {
        $accepted = LegalAcceptance::query()
            ->where('user_id', $user->getKey())
            ->get(['document', 'version'])
            ->map(fn (LegalAcceptance $row) => "{$row->document}@{$row->version}")
            ->all();

        return collect($this->currentVersions())
            ->filter(fn (string $version, string $document) => ! in_array("{$document}@{$version}", $accepted, true))
            ->keys()
            ->values()
            ->all();
    }

    /** Estado que recibe el frontend para decidir si debe pedir la aceptación. */
    public function statusFor(User $user): array
    {
        $latest = LegalAcceptance::query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('accepted_at')
            ->get()
            ->unique('document')
            ->keyBy('document');

        $pending = $this->pendingFor($user);

        return [
            'pending' => $pending,
            'documents' => collect($this->currentVersions())
                ->map(fn (string $version, string $document) => [
                    'version' => $version,
                    'accepted' => ! in_array($document, $pending, true),
                    'accepted_version' => $latest->get($document)?->version,
                    'accepted_at' => $latest->get($document)?->accepted_at?->toISOString(),
                ])
                ->all(),
        ];
    }

    /** Registra la aceptación de la versión vigente de cada documento indicado. */
    public function accept(User $user, array $documents, Request $request): void
    {
        $versions = $this->currentVersions();

        foreach (array_unique($documents) as $document) {
            if (! isset($versions[$document])) {
                continue;
            }

            LegalAcceptance::query()->firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'document' => $document,
                    'version' => $versions[$document],
                ],
                [
                    'accepted_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
                ]
            );
        }
    }
}
