<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->patient()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('patient.profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->patient()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('patient.profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->patient()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('patient.profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_breeze_profile_route_is_not_exposed(): void
    {
        $user = User::factory()->patient()->create();

        $this->actingAs($user)->get('/profile')->assertNotFound();
        $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertNotFound();
        $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ])->assertNotFound();
    }
}
