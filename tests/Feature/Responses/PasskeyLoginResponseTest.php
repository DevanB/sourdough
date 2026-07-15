<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Http\Responses\PasskeyLoginResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

it('returns a json redirect payload', function (): void {
    $user = User::factory()->create();

    $request = Request::create('/passkeys/login', 'POST');
    $request->headers->set('Accept', 'application/json');
    $request->setUserResolver(fn () => $user);

    $response = resolve(PasskeyLoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getData(true))->toBe([
            'redirect' => url(route('dashboard', absolute: false)),
        ]);
});

it('targets team select when the user has multiple teams', function (): void {
    $user = User::factory()->create();
    resolve(CreateTeam::class)->handle($user, 'Acme');

    $request = Request::create('/passkeys/login', 'POST');
    $request->headers->set('Accept', 'application/json');
    $request->setUserResolver(fn () => $user);

    $response = resolve(PasskeyLoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getData(true))->toBe([
            'redirect' => url(route('team-select.show', absolute: false)),
        ]);
});
