<?php

declare(strict_types=1);

use App\Models\TeamInvitation;
use App\Models\User;

it('exposes expiry days constant', function (): void {
    expect(TeamInvitation::EXPIRY_DAYS)->toBe(3);
});

it('is accepted when accepted_at is set', function (): void {
    $invitation = TeamInvitation::factory()->accepted()->create([
        'invited_by' => User::factory(),
    ]);

    expect($invitation->isAccepted())->toBeTrue()
        ->and($invitation->isPending())->toBeFalse();
});

it('is expired when expires_at is in the past', function (): void {
    $invitation = TeamInvitation::factory()->expired()->create([
        'invited_by' => User::factory(),
    ]);

    expect($invitation->isExpired())->toBeTrue()
        ->and($invitation->isPending())->toBeFalse();
});

it('is pending when not accepted and not expired', function (): void {
    $invitation = TeamInvitation::factory()->create([
        'invited_by' => User::factory(),
    ]);

    expect($invitation->isPending())->toBeTrue()
        ->and($invitation->isAccepted())->toBeFalse()
        ->and($invitation->isExpired())->toBeFalse();
});
