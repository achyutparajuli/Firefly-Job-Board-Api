<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test logout with valid authentication token.
     *
     * @return void
     */
    public function test_logout_with_valid_token()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
            'mobile' => '9898989894',
            'job_title' => 'laravel dev'
        ]);

        // Generate a token for the user
        Passport::actingAs($user);

        // Send a POST request to logout endpoint with the token
        $response = $this->postJson('/api/v1/logout');

        // Assert that the response has a 200 status code
        $response->assertStatus(200);
    }

    /**
     * Test logout without authentication token.
     *
     * @return void
     */
    public function test_logout_without_token()
    {
        // Send a POST request to logout endpoint without token
        $response = $this->postJson('/api/v1/logout');

        // Assert that the response has a 401 status code (Unauthorized)
        $response->assertStatus(401);
    }
}
