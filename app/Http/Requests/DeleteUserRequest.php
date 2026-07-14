<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class DeleteUserRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->user();

                if ($user === null) {
                    return;
                }

                $ownsSharedTeam = $user->ownedTeams()
                    ->withCount('members')
                    ->get()
                    ->contains(fn ($team): bool => $team->members_count > 1);

                if ($ownsSharedTeam) {
                    $validator->errors()->add(
                        'password',
                        __('You must transfer or delete teams with other members before deleting your account.'),
                    );
                }
            },
        ];
    }
}
