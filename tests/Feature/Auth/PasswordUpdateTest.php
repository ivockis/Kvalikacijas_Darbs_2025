<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    // Helper method for CSRF token retrieval for profile page
    protected function getCsrfTokenForProfile(User $user): string
    {
        $get_response = $this->actingAs($user)->get('/profile');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the profile form.');
        return $token;
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();
        $token = $this->getCsrfTokenForProfile($user);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
                '_token' => $token, // Add the CSRF token
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();
        $token = $this->getCsrfTokenForProfile($user);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
                '_token' => $token, // Add the CSRF token
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }
}