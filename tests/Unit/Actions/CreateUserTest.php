<?php

declare(strict_types=1);

use App\Actions\CreateUser;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

it('may create a user', function (): void {
    Event::fake([Registered::class]);

    $action = resolve(CreateUser::class);

    $user = $action->handle([
        'name' => 'Test User',
        'email' => 'example@email.com',
    ], 'password');

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('example@email.com')
        ->and($user->password)->not->toBe('password')
        ->and($user->personalTeam())->not->toBeNull()
        ->and($user->personalTeam()->name)->toBe("Test User's Team")
        ->and($user->personalTeam()->is_personal)->toBeTrue()
        ->and($user->current_team_id)->toBe($user->personalTeam()->id);

    Event::assertDispatched(Registered::class);
});
