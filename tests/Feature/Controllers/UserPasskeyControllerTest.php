<?php

declare(strict_types=1);

use App\Models\User;

it('renders passkeys page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->session(['auth.password_confirmed_at' => time()]);

    $response = $this->fromRoute('dashboard')
        ->get(route('passkeys.show'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user-passkey/show')
            ->where('canManagePasskeys', true)
            ->where('passkeys', []));
});

it('lists the passkeys of the user', function (): void {
    $user = User::factory()->create();

    $passkey = $user->passkeys()->create([
        'name' => 'MacBook Pro',
        'credential_id' => 'credential-id',
        'credential' => ['aaguid' => '00000000-0000-0000-0000-000000000000'],
    ]);

    $passkey->forceFill([
        'created_at' => now()->subDay(),
        'last_used_at' => now()->subHour(),
    ])->save();

    $this->actingAs($user)->session(['auth.password_confirmed_at' => time()]);

    $response = $this->fromRoute('dashboard')
        ->get(route('passkeys.show'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user-passkey/show')
            ->where('passkeys.0.id', $passkey->id)
            ->where('passkeys.0.name', 'MacBook Pro')
            ->where('passkeys.0.authenticator', null)
            ->where('passkeys.0.created_at_diff', '1 day ago')
            ->where('passkeys.0.last_used_at_diff', '1 hour ago'));
});

it('requires password confirmation', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('passkeys.show'));

    $response->assertRedirect(route('password.confirm'));
});

it('may not be visited by guests', function (): void {
    $response = $this->get(route('passkeys.show'));

    $response->assertRedirect(route('login'));
});

it('exposes the passkey endpoints for discovery', function (): void {
    $response = $this->get(route('well-known.passkeys'));

    $response->assertOk()
        ->assertExactJson([
            'enroll' => route('passkeys.show'),
            'manage' => route('passkeys.show'),
        ]);
});

it('throttles passkey login options requests', function (): void {
    $url = route('passkey.login-options', ['credential' => ['id' => 'credential-id']]);

    foreach (range(1, 10) as $attempt) {
        $this->get($url)->assertOk();
    }

    $this->get($url)->assertTooManyRequests();
});
