<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Features;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $teamsEnabled = Features::teams();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'features' => [
                'teams' => $teamsEnabled,
            ],
            'currentTeam' => fn (): ?array => $teamsEnabled && $user?->currentTeam
                ? $user->toUserTeam($user->currentTeam)->toArray()
                : null,
            'teams' => fn (): array => $teamsEnabled && $user
                ? $user->userTeams()->map->toArray()->all()
                : [],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
