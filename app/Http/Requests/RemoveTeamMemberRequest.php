<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use LogicException;

final class RemoveTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('removeMember', [$this->team(), $this->member()]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }

    private function team(): Team
    {
        $team = $this->route('team');

        if (! $team instanceof Team) {
            throw new LogicException('The team route parameter must be a team model.');
        }

        return $team;
    }

    private function member(): User
    {
        $member = $this->route('member');

        if (! $member instanceof User) {
            throw new LogicException('The member route parameter must be a user model.');
        }

        return $member;
    }
}
