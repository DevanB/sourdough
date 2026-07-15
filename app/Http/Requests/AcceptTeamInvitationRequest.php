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

        return strcasecmp($invitation->email, $user->email) === 0;
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

        if (! $invitation instanceof TeamInvitation) {
            throw new LogicException('The invitation route parameter must be a team invitation model.');
        }

        return $invitation;
    }
}
