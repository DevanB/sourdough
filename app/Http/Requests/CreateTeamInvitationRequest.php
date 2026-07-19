<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TeamRole;
use App\Models\Team;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LogicException;

final class CreateTeamInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('inviteMember', $this->team());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::enum(TeamRole::class)->only([TeamRole::Admin, TeamRole::Member])],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $team = $this->team();
                $email = mb_strtolower($this->string('email')->value());

                $isMember = $team->members()
                    ->whereRaw('LOWER(users.email) = ?', [$email])
                    ->exists();

                if ($isMember) {
                    $validator->errors()->add('email', __('This user is already a team member.'));
                }

                $hasPending = $team->invitations()
                    ->whereNull('accepted_at')
                    ->where('expires_at', '>', now())
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->exists();

                if ($hasPending) {
                    $validator->errors()->add('email', __('An invitation has already been sent to this email.'));
                }
            },
        ];
    }

    private function team(): Team
    {
        $team = $this->route('team');

        throw_unless($team instanceof Team, LogicException::class, 'The team route parameter must be a team model.');

        return $team;
    }
}
