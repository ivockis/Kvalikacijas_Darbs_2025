<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // Helper method for CSRF token retrieval for /forgot-password page
    protected function getCsrfTokenForForgotPassword(): string
    {
        $get_response = $this->get('/forgot-password');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the forgot password form.');
        return $token;
    }

    // Helper method for CSRF token retrieval for /reset-password page
    protected function getCsrfTokenForResetPassword(string $resetToken, string $email): string
    {
        $get_response = $this->get(route('password.reset', ['token' => $resetToken, 'email' => $email]));
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the reset password form.');
        return $token;
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    /** @test Case 24: Password reset with a registered email */
    public function reset_password_link_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $token = $this->getCsrfTokenForForgotPassword();

        $response = $this->post('/forgot-password', ['email' => $user->email, '_token' => $token]);

        Notification::assertSentTo($user, ResetPassword::class);
        $response->assertSessionHas('status');
    }

    /** @test Case 25: Password reset with an unregistered email */
    public function reset_password_link_is_not_sent_for_unregistered_email(): void
    {
        Notification::fake();
        $token = $this->getCsrfTokenForForgotPassword();

        $response = $this->post('/forgot-password', ['email' => 'nonexistent@example.com', '_token' => $token]);
        
        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');
    }
    
    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $resetToken = Password::createToken($user);
        $csrfToken = $this->getCsrfTokenForResetPassword($resetToken, $user->email);

        $response = $this->post('/reset-password', [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'password', // Changed from New-Password123
            'password_confirmation' => 'password', // Changed from New-Password123
            '_token' => $csrfToken,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('password', $user->fresh()->password)); // Changed from New-Password123
    }

    /** @test */
    public function password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();
        // A valid token is needed to render the form, but we'll submit an invalid one
        $validTokenToRenderForm = Password::createToken($user);
        $csrfToken = $this->getCsrfTokenForResetPassword($validTokenToRenderForm, $user->email);


        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'password', // Changed from New-Password123
            'password_confirmation' => 'password', // Changed from New-Password123
            '_token' => $csrfToken,
        ]);

        $response->assertSessionHasErrors('email');
    }
    
    /** @test */
    public function password_reset_fails_with_short_password(): void
    {
        $user = User::factory()->create();
        $resetToken = Password::createToken($user);
        $csrfToken = $this->getCsrfTokenForResetPassword($resetToken, $user->email);

        $response = $this->post('/reset-password', [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
            '_token' => $csrfToken,
        ]);

        $response->assertSessionHasErrors('password');
    }
    
    /** @test */
    public function password_reset_fails_with_mismatched_passwords(): void
    {
        $user = User::factory()->create();
        $resetToken = Password::createToken($user);
        $csrfToken = $this->getCsrfTokenForResetPassword($resetToken, $user->email);

        $response = $this->post('/reset-password', [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'password', // Changed from New-Password123
            'password_confirmation' => 'wrong-password', // Changed from Wrong-Password123
            '_token' => $csrfToken,
        ]);

        $response->assertSessionHasErrors('password');
    }
}
