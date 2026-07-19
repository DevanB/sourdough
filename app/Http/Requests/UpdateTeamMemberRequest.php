<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use LogicException;

final class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('updateMember', [$this->team(), $this->member()]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::enum(TeamRole::class)->only([TeamRole::Admin, TeamRole::Member])],
        ];
    }

    private function team(): Team
    {
        $team = $this->route('team');

        throw_unless($team instanceof Team, LogicException::class, 'The team route parameter must be a team model.');

        return $team;
    }

    private function member(): User
    {
        $member = $this->route('member');

        throw_unless($member instanceof User, LogicException::class, 'The member route parameter must be a user model.');

        return $member;
    }
}
