<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->get('/reset-password/'.$notification->token.'?email='.$user->email);

            $response
                ->assertStatus(200)
                ->assertSee('Nouveau mot de passe')
                ->assertSee($user->name)
                ->assertDontSee('Laravel')
                ->assertDontSee('CNDP')
                ->assertDontSee('Karim Tazi')
                ->assertDontSee('TKN-8831')
                ->assertDontSee('Atlas#2024Health!');

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('password.updated'));

            $this->get(route('password.updated'))
                ->assertOk()
                ->assertSee('Mot de passe modifié')
                ->assertSee($user->name)
                ->assertDontSee('CNDP')
                ->assertDontSee('HDS')
                ->assertDontSee('AES')
                ->assertDontSee('HIPAA')
                ->assertDontSee('Karim Tazi')
                ->assertDontSee('05 22 89 00 00')
                ->assertDontSee('MA-739201')
                ->assertDontSee('8F2a-99X')
                ->assertDontSee('Argon2id');

            return true;
        });
    }

    public function test_password_updated_page_requires_reset_session(): void
    {
        $this->get(route('password.updated'))
            ->assertRedirect(route('login'));
    }
}
