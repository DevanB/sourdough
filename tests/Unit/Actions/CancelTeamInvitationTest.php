<?php

declare(strict_types=1);

use App\Actions\CancelTeamInvitation;
use App\Actions\CreateTeam;
use App\Models\TeamInvitation;
use App\Models\User;

it('may cancel a team invitation', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    resolve(CancelTeamInvitation::class)->handle($invitation);

    expect($invitation->fresh())->toBeNull();
});
