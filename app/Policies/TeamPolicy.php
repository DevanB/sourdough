<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

final class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function update(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, 'team:update');
    }

    public function addMember(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, 'member:add');
    }

    public function inviteMember(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, 'invitation:create');
    }

    public function cancelInvitation(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, 'invitation:cancel');
    }

    public function delete(User $user, Team $team): bool
    {
        return ! $team->is_personal && $user->hasTeamPermission($team, 'team:delete');
    }

    public function updateMember(User $user, Team $team, User $member): bool
    {
        return $user->hasTeamPermission($team, 'member:update')
            && ! $member->ownsTeam($team);
    }

    public function removeMember(User $user, Team $team, User $member): bool
    {
        if ($member->ownsTeam($team)) {
            return false;
        }

        if ($user->hasTeamPermission($team, 'member:remove')) {
            return true;
        }

        return $user->is($member);
    }
}
