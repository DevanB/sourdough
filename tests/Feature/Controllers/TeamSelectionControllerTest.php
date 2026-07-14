<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Models\User;

it('renders team selection when the user has multiple teams', function (): void {
    $user = User::factory()->create();
    resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('team-select.show'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('team-select/show')
            ->has('teams', 2));
});

it('redirects to the dashboard when the user has fewer than two teams', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('team-select.show'));

    $response->assertRedirectToRoute('dashboard');
});
