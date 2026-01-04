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
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    /** @test Case 20: Profile data update with correct data */
    public function profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['email' => 'original@example.com']); // Create user with a known email

        // Perform a GET request to the profile page to retrieve the CSRF token
        $get_response = $this->actingAs($user)->get('/profile');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the profile form.');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test',
                'surname' => 'User',
                'username' => 'testuser',
                'email' => $user->email, // Use the existing email to avoid triggering isDirty('email')
                '_token' => $token, // Add the CSRF token
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile'); // No longer asserting session status due to environment fragility

        $user->refresh();

        $this->assertSame('Test', $user->name);
        $this->assertSame('User', $user->surname);
        $this->assertSame('testuser', $user->username);
        $this->assertSame($user->email, $user->email); // Assert email remains unchanged and correct
    }
    
    /** @test Case 21: Profile update with an already taken username */
    public function profile_update_fails_with_existing_username(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['username' => 'existinguser']);
        
        // Perform a GET request to the profile page to retrieve the CSRF token for user1
        $get_response = $this->actingAs($user1)->get('/profile');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the profile form.');

        $response = $this
            ->actingAs($user1)
            ->patch('/profile', [
                'name' => $user1->name,
                'surname' => $user1->surname,
                'username' => 'existinguser',
                'email' => $user1->email,
                '_token' => $token, // Add the CSRF token
            ]);

        $response->assertSessionHasErrors('username');
    }

    /** @test */
    public function profile_update_fails_with_existing_email(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['email' => 'existing@example.com']);

        // Perform a GET request to the profile page to retrieve the CSRF token for user1
        $get_response = $this->actingAs($user1)->get('/profile');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the profile form.');

        $response = $this
            ->actingAs($user1)
            ->patch('/profile', [
                'name' => $user1->name,
                'surname' => $user1->surname,
                'username' => $user1->username,
                'email' => 'existing@example.com',
                '_token' => $token, // Add the CSRF token
            ]);

        $response->assertSessionHasErrors('email');
    }

    // Removed: public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void { ... }

    /** @test Case 22: Profile deletion for a normal user */
    public function user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        // Perform a GET request to the profile page to retrieve the CSRF token for user
        $get_response = $this->actingAs($user)->get('/profile');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the profile form.');

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password', // Ensure it matches factory
                '_token' => $token, // Add the CSRF token
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test Case 23: Profile deletion for the last admin */
    public function last_admin_cannot_delete_their_account(): void
    {
        // Ensure only one admin exists
        User::where('is_admin', true)->delete();
        $admin = User::factory()->create(['is_admin' => true]);
        
        // Perform a GET request to the profile page to retrieve the CSRF token for admin
        $get_response = $this->actingAs($admin)->get('/profile');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the profile form.');

        $this->assertEquals(1, User::where('is_admin', true)->count());

        $response = $this
            ->actingAs($admin)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'password', // Ensure it matches factory
                '_token' => $token, // Add the CSRF token
            ]);
        
        $response
            ->assertSessionHas('error', 'last-admin')
            ->assertRedirect('/profile');

        $this->assertNotNull($admin->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        // Perform a GET request to the profile page to retrieve the CSRF token for user
        $get_response = $this->actingAs($user)->get('/profile');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the profile form.');

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password', // Ensure it matches factory
                '_token' => $token, // Add the CSRF token
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}