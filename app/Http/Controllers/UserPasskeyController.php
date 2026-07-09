<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Passkey;

final readonly class UserPasskeyController implements HasMiddleware
{
    public static function middleware(): array
    {
        return Features::optionEnabled(Features::passkeys(), 'confirmPassword')
            ? [new Middleware('password.confirm', only: ['show'])]
            : [];
    }

    public function show(#[CurrentUser] User $user): Response
    {
        return Inertia::render('user-passkey/show', [
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => $user->passkeys()
                ->latest()
                ->get()
                ->map($this->passkeyProps(...))
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array{id: int, name: string, authenticator: string|null, created_at_diff: string|null, last_used_at_diff: string|null}
     */
    private function passkeyProps(Passkey $passkey): array
    {
        return [
            'id' => $passkey->id,
            'name' => $passkey->name,
            'authenticator' => $passkey->authenticator,
            'created_at_diff' => $passkey->created_at?->diffForHumans(),
            'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
        ];
    }
}
