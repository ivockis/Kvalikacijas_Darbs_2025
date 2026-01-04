<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    // Helper method for CSRF token retrieval for admin user management page
    protected function getCsrfTokenForAdminUsersIndex(): string
    {
        $get_response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the admin users index page.');
        return $token;
    }

    /** @test Case 27: A normal user tries to access the admin user management panel */
    public function non_admin_user_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.users.index'));
        $response->assertForbidden();
    }
    
    /** @test */
    public function admin_user_can_access_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertOk();
    }

    /** @test Case 28: Granting admin rights */
    public function admin_can_grant_admin_rights(): void
    {
        $token = $this->getCsrfTokenForAdminUsersIndex(); // Get token

        $response = $this->actingAs($this->admin)->patch(route('admin.users.toggleAdmin', $this->user), ['_token' => $token]);
        
        $response->assertOk();
        $response->assertJsonFragment(['is_admin' => true]);
        $this->assertTrue($this->user->fresh()->is_admin);
    }
    
    /** @test */
    public function admin_can_revoke_admin_rights(): void
    {
        $this->user->update(['is_admin' => true]);
        $this->assertTrue($this->user->is_admin);
        
        $token = $this->getCsrfTokenForAdminUsersIndex(); // Get token
        $response = $this->actingAs($this->admin)->patch(route('admin.users.toggleAdmin', $this->user), ['_token' => $token]);
        
        $response->assertOk();
        $response->assertJsonFragment(['is_admin' => false]);
        $this->assertFalse($this->user->fresh()->is_admin);
    }

    /** @test Case 26: Blocking a user */
    public function admin_can_block_a_user(): void
    {
        $this->assertFalse($this->user->is_blocked);
        
        $token = $this->getCsrfTokenForAdminUsersIndex(); // Get token
        $response = $this->actingAs($this->admin)->patch(route('admin.users.toggleBlock', $this->user), ['_token' => $token]);
        
        $response->assertOk();
        $response->assertJsonFragment(['is_blocked' => true]);
        $this->assertTrue($this->user->fresh()->is_blocked);
    }

    /** @test */
    public function admin_can_unblock_a_user(): void
    {
        $this->user->update(['is_blocked' => true]);
        $this->assertTrue($this->user->is_blocked);
        
        $token = $this->getCsrfTokenForAdminUsersIndex(); // Get token
        $response = $this->actingAs($this->admin)->patch(route('admin.users.toggleBlock', $this->user), ['_token' => $token]);
        
        $response->assertOk();
        $response->assertJsonFragment(['is_blocked' => false]);
        $this->assertFalse($this->user->fresh()->is_blocked);
    }
    
    /** @test Case 29: Searching for users */
    public function admin_can_search_users_by_username(): void
    {
        $userToFind = User::factory()->create(['username' => 'findme']);
        $userToIgnore = User::factory()->create(['username' => 'hideme']);
        
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'findme']));
        
        $response->assertOk();
        $response->assertSee($userToFind->username);
        $response->assertDontSee($userToIgnore->username);
    }
    
    /** @test Case 29: Searching for users */
    public function admin_can_search_users_by_email(): void
    {
        $userToFind = User::factory()->create(['email' => 'findme@example.com']);
        $userToIgnore = User::factory()->create(['email' => 'hideme@example.com']);
        
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'findme@example.com']));
        
        $response->assertOk();
        $response->assertSee($userToFind->email);
        $response->assertDontSee($userToIgnore->email);
    }
}
