<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Team;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;
use LogicException;

final class DeleteTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->team());
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('name') !== $this->team()->name) {
                    $validator->errors()->add('name', __('The team name does not match.'));
                }
            },
        ];
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
