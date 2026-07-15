<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\DeleteUser;
use App\Models\User;

it('may delete a user', function (): void {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();

    $action = resolve(DeleteUser::class);

    $action->handle($user);

    expect($user->exists)->toBeFalse()
        ->and($personalTeam->fresh())->toBeNull();
});

it('deletes owned teams when deleting a user', function (): void {
    $user = User::factory()->create();
    $sharedTeam = resolve(CreateTeam::class)->handle($user, 'Acme');

    resolve(DeleteUser::class)->handle($user);

    expect($user->fresh())->toBeNull()
        ->and($sharedTeam->fresh())->toBeNull();
});
