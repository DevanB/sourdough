<?php

declare(strict_types=1);

use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'two_factor_confirmed_at',
            'created_at',
            'updated_at',
        ]);
});

test('may have passkeys', function (): void {
    $user = User::factory()->create();

    expect($user->hasPasskeysEnabled())->toBeFalse();

    $user->passkeys()->create([
        'name' => 'MacBook Pro',
        'credential_id' => 'credential-id',
        'credential' => [],
    ]);

    expect($user->hasPasskeysEnabled())->toBeTrue()
        ->and($user->passkeys()->count())->toBeOne();
});
