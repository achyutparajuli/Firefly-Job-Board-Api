<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use App\Models\User;

class UserLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_login_passing_inactive_status()
    {
        $num = rand(1, 99999);
        // Create a user
        $user = User::factory()->create([
            'email' => 'test' . $num . '@example.com',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev'
        ]);

        // Attempt login
        $response = $this->post('/api/v1/login', [
            'email' => 'test' . $num . '@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422); // because the user doesnt has verified themself
    }

    public function test_user_login_passing_active_status()
    {
        $num = rand(1, 99999);
        // Create a user
        $user = User::factory()->create([
            'email' => 'test' . $num . '@example.com',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev',
            'status' => 1
        ]);

        // Attempt login
        $response = $this->post('/api/v1/login', [
            'email' => 'test' . $num . '@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200); // because the user is verified as we have sent status true
    }

    public function test_user_login_passing_wrong_password()
    {
        $num = rand(1, 99999);
        // Create a user
        $user = User::factory()->create([
            'email' => 'test' . $num . '@example.com',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev',
            'status' => 1
        ]);

        // Attempt login
        $response = $this->post('/api/v1/login', [
            'email' => 'test' . $num . '@example.com',
            'password' => 'passwordd',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_login_without_email_verification()
    {
        $num = rand(1, 99999);
        // Create a user
        $user = User::factory()->create([
            'email' => 'test' . $num . '@example.com',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '81',
            'job_title' => 'laravel dev',
            'status' => 1,
            'email_verified_at' => NUll
        ]);

        // Attempt login
        $response = $this->post('/api/v1/login', [
            'email' => 'test' . $num . '@example.com',
            'password' => 'password',
        ]);
        $response->assertStatus(422);
    }
}
