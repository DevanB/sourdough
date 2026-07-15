<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use LogicException;

final class SwitchTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('view', $this->team());
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
}
