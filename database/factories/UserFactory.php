<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Actions\CreateTeam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use SplObjectStorage;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * @var SplObjectStorage<User, true>|null
     */
    private static ?SplObjectStorage $withoutPersonalTeam = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => Str::random(10),
            'two_factor_secret' => Str::random(10),
            'two_factor_recovery_codes' => Str::random(10),
            'two_factor_confirmed_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if (self::$withoutPersonalTeam?->offsetExists($user) === true) {
                self::$withoutPersonalTeam->offsetUnset($user);

                return;
            }

            resolve(CreateTeam::class)->handle($user, $user->name."'s Team", isPersonal: true);
        });
    }

    public function withoutPersonalTeam(): self
    {
        return $this->afterMaking(function (User $user): void {
            self::$withoutPersonalTeam ??= new SplObjectStorage;
            self::$withoutPersonalTeam[$user] = true;
        });
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withoutTwoFactor(): self
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
