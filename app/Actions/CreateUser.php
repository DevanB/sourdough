<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use SensitiveParameter;

final readonly class CreateUser
{
    public function __construct(private CreateTeam $createTeam) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, #[SensitiveParameter] string $password): User
    {
        return DB::transaction(function () use ($attributes, $password): User {
            $user = User::query()->create([
                ...$attributes,
                'password' => $password,
            ]);

            $this->createTeam->handle($user, "{$user->name}'s Team", isPersonal: true);

            event(new Registered($user));

            return $user->refresh();
        });
    }
}
