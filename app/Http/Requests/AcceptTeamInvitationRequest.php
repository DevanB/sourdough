<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\TeamInvitation;
use Illuminate\Foundation\Http\FormRequest;
use LogicException;

final class AcceptTeamInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invitation = $this->invitation();
        $user = $this->user();

        if ($user === null || ! $invitation->isPending()) {
            return false;
        }

        return strcasecmp($invitation->email, (string) $user->email) === 0;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }

    private function invitation(): TeamInvitation
    {
        $invitation = $this->route('invitation');

        throw_unless($invitation instanceof TeamInvitation, LogicException::class, 'The invitation route parameter must be a team invitation model.');

        return $invitation;
    }
}
