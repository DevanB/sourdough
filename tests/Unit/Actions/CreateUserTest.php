<?php

declare(strict_types=1);

use App\Actions\CreateUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

it('may create a user', function (): void {
    Event::fake([Registered::class]);

    $action = resolve(CreateUser::class);

    $user = $action->handle([
        'name' => 'Test User',
        'email' => 'example@email.com',
    ], 'password');

    $personalTeam = $user->personalTeam();
    $this->assertNotNull($personalTeam);

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('example@email.com')
        ->and($user->password)->not->toBe('password')
        ->and($personalTeam->name)->toBe("Test User's Team")
        ->and($personalTeam->is_personal)->toBeTrue()
        ->and($user->current_team_id)->toBe($personalTeam->id);

    Event::assertDispatched(Registered::class);
});
