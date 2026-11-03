<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_authentication_pages_and_are_redirected_from_the_workspace(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('password.request'))->assertOk();
        $this->get(route('dormitory.dashboard'))->assertRedirect(route('login'));
        $this->get(route('dormitory.students.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_register_and_is_signed_in_with_a_normalized_email(): void
    {
        $response = $this->post(route('register'), [
            'name' => ' Dormitory Manager ',
            'email' => ' Manager@Example.com ',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ]);

        $response->assertRedirect(route('dormitory.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Dormitory Manager',
            'email' => 'manager@example.com',
        ]);
        $this->assertTrue(Hash::check('secure-password', User::query()->sole()->password));
    }

    public function test_registration_rejects_duplicate_email_and_short_password(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post(route('register'), [
            'name' => 'Another Manager',
            'email' => 'existing@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_user_can_sign_in_and_sign_out(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('dormitory.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->withSession(['auth_marker' => 'private'])
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing('auth_marker');

        $this->assertGuest();
    }

    public function test_invalid_credentials_do_not_sign_in(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_request_and_complete_a_password_reset(): void
    {
        $user = User::factory()->create();
        $resetToken = null;
        Notification::fake();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an account exists for that email, a password reset link has been sent.');

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification) use (&$resetToken): bool {
                $resetToken = $notification->token;

                return true;
            },
        );

        $this->get(route('password.reset', [
            'token' => $resetToken,
            'email' => $user->email,
        ]))->assertOk();

        $this->post(route('password.update'), [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_password_reset_confirmation_does_not_disclose_unknown_emails(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'unknown@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an account exists for that email, a password reset link has been sent.');

        Notification::assertNothingSent();
    }

    public function test_password_reset_rejects_an_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->from(route('password.reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
        ]))
            ->post(route('password.update'), [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
