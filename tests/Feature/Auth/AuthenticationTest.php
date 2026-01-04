<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // New helper method for CSRF token retrieval for login
    protected function getCsrfTokenForLogin(): string
    {
        $get_response = $this->get('/login');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the login form.');
        return $token;
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test Case 14: Login with correct data */
    public function users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();
        $token = $this->getCsrfTokenForLogin();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password', // Corrected password
            '_token' => $token,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('public.index'));
    }

    /** @test Case 15: Login with non-existing email */
    public function users_can_not_authenticate_with_non_existing_email(): void
    {
        $token = $this->getCsrfTokenForLogin();

        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password', // Corrected password
            '_token' => $token,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
    
    /** @test Case 16: Login with incorrect password */
    public function users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();
        $token = $this->getCsrfTokenForLogin();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password', // Corrected password
            '_token' => $token,
        ]);
        
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test Case 17: Login for a blocked user */
    public function blocked_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_blocked' => true]);
        $token = $this->getCsrfTokenForLogin();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password', // Corrected password
            '_token' => $token,
        ]);
        
        // Assert that the login fails with the 'auth.blocked' message
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrorsIn('default', 'email', trans('auth.blocked'));
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /** @test Case 18: Successful logout */
    public function users_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $this->getCsrfTokenForLogin();

        $response = $this->actingAs($user)->post('/logout', ['_token' => $token]);

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /** @test Case 19: Logout without active session */
    public function unauthenticated_user_is_redirected_from_logout(): void
    {
        $token = $this->getCsrfTokenForLogin();

        $response = $this->post('/logout', ['_token' => $token]);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}