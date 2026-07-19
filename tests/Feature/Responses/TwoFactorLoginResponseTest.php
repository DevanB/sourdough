<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Http\Responses\TwoFactorLoginResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

it('returns an empty json response', function (): void {
    $user = User::factory()->create();

    $request = Request::create('/two-factor-challenge', 'POST');
    $request->headers->set('Accept', 'application/json');
    $request->setUserResolver(fn (): User => $user);

    $response = resolve(TwoFactorLoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(204);
});

it('redirects to the dashboard', function (): void {
    $user = User::factory()->create();

    $request = Request::create('/two-factor-challenge', 'POST');
    $request->setUserResolver(fn (): User => $user);

    $response = resolve(TwoFactorLoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(url(route('dashboard', absolute: false)));
});

it('redirects to team select when the user has multiple teams', function (): void {
    $user = User::factory()->create();
    resolve(CreateTeam::class)->handle($user, 'Acme');

    $request = Request::create('/two-factor-challenge', 'POST');
    $request->setUserResolver(fn (): User => $user);

    $response = resolve(TwoFactorLoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(url(route('team-select.show', absolute: false)));
});

it('falls back to the dashboard when no user is authenticated', function (): void {
    $request = Request::create('/two-factor-challenge', 'POST');

    $response = resolve(TwoFactorLoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(url(route('dashboard', absolute: false)));
});
