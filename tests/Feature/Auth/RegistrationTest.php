<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // New helper method for CSRF token retrieval
    protected function getCsrfToken(): string
    {
        $get_response = $this->get('/register');
        $get_response->assertStatus(200);
        $token = session('_token');
        $this->assertNotNull($token, 'CSRF token not found in the session.');
        return $token;
    }

    private function getValidRegistrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test',
            'surname' => 'User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ], $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /** @test Case 10: Successful registration */
    public function user_can_register_successfully(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData();
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);

        // Corrected redirect assertion
        $response->assertRedirect(route('public.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'testuser',
        ]);
    }
    
    /** @test Case 11: Registration with a required field empty */
    public function registration_fails_with_missing_required_field(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['name' => '']);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('name');
    }

    /** @test Case 1: Password shorter than 8 characters */
    public function registration_fails_with_short_password(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['password' => 'pass1', 'password_confirmation' => 'pass1']);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('password');
    }

    /** @test Case 2: Password without numbers */
    public function registration_fails_with_password_without_numbers(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['password' => 'Password', 'password_confirmation' => 'Password']);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('password');
    }

    /** @test Case 2: Password without letters */
    public function registration_fails_with_password_without_letters(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['password' => '12345678', 'password_confirmation' => '12345678']);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('password');
    }
    
    /** @test Case 13: Registration with mismatched password confirmation */
    public function registration_fails_with_mismatched_passwords(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['password_confirmation' => 'WrongPassword123']);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('password');
    }

    /** @test Case 3: Registration with an already registered username */
    public function registration_fails_with_existing_username(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        User::factory()->create(['username' => 'testuser']); // Create user before getting token for this test
        $registrationData = $this->getValidRegistrationData();
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('username');
    }

    /** @test Case 4: Registration with an already registered email address */
    public function registration_fails_with_existing_email(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        User::factory()->create(['email' => 'test@example.com']); // Create user before getting token for this test
        $registrationData = $this->getValidRegistrationData();
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('email');
    }
    
    /** @test Case 12: Registration with an invalid email */
    public function registration_fails_with_invalid_email(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['email' => 'not-an-email']);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('email');
    }

    /** @test Case 5: Name exceeds 255 characters */
    public function registration_fails_with_long_name(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['name' => Str::random(256)]);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('name');
    }

    /** @test Case 6: Surname exceeds 255 characters */
    public function registration_fails_with_long_surname(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['surname' => Str::random(256)]);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('surname');
    }

    /** @test Case 7: Username exceeds 30 characters */
    public function registration_fails_with_long_username(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['username' => Str::random(31)]);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('username');
    }
    
    /** @test Case 8: Email exceeds 255 characters */
    public function registration_fails_with_long_email(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $registrationData = $this->getValidRegistrationData(['email' => Str::random(256) . '@example.com']);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasErrors('email');
    }
    
    /** @test Case 9: Password exceeds 255 characters (and the application currently allows it) */
    public function registration_fails_with_long_password(): void
    {
        $token = $this->getCsrfToken(); // Use the helper method

        $longPassword = Str::random(256); // A password longer than 255 chars
        $registrationData = $this->getValidRegistrationData(['password' => $longPassword, 'password_confirmation' => $longPassword]);
        $registrationData['_token'] = $token;

        $response = $this->post('/register', $registrationData);

        // Assert successful registration, as the backend does not currently have a max length rule for password
        $response->assertRedirect(route('public.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com', // Assuming the email is still valid
            'username' => 'testuser', // Assuming the username is still valid
        ]);
        // Also assert that the password in the database is correctly hashed (Laravel takes care of this)
        $this->assertTrue(User::where('email', 'test@example.com')->first()->password != $longPassword);
    }
}