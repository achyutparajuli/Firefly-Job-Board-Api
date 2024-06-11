<?php

namespace Tests\Feature\Employer;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateNewJobTest extends TestCase
{
    public $employerUser, $employeeUser;
    /**
     * A basic feature test example.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Run the Passport install command programmatically
        Artisan::call('passport:install');

        $num = rand(1, 99999);
        $this->employerUser = User::create([
            'id' => $num,
            'email' => 'test' . $num . '@example.com',
            'name' => 'name',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev',
            'user_type' => 'employer'
        ]);

        $num1 = rand(1, 99999);
        $this->employeeUser = User::create([
            'id' => $num1,
            'email' => 'test' . $num1 . '@example.com',
            'name' => 'name',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num1 . '1',
            'job_title' => 'laravel dev',
            'user_type' => 'employee'
        ]);
    }

    public function test_create_new_jobs_without_token()
    {
        $response = $this->postJson('/api/v1/employer/jobs', []);
        $response->assertStatus(401);
    }

    public function test_create_new_jobs_with_valid_token()
    {
        // Authenticate the user
        Passport::actingAs($this->employerUser);

        $response = $this->postJson('/api/v1/employer/jobs', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employerUser->createToken('TestToken')->accessToken, 'title' => 'Software Developer',
            'company_name' => 'Tech Solutions',
            'location' => 'New York',
            'description' => 'Job description goes here',
            'instruction' => 'Instructions for applicants',
            'deadline' => now()->addDays(10)->toDateString(),
            'keywords' => 'PHP, Laravel, Developer',
        ]);

        // Assert the response
        $response->assertStatus(201);
    }

    public function test_create_new_jobs_with_valid_token_with_incomplete_inputs()
    {
        // Authenticate the user
        Passport::actingAs($this->employerUser);

        $response = $this->postJson('/api/v1/employer/jobs', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employerUser->createToken('TestToken')->accessToken, 'title' => 'Software Developer',
        ]);

        // Assert the response
        $response->assertStatus(422);
    }

    public function test_create_new_jobs_with_valid_token_with_wrong_user_role()
    {
        // Authenticate the user
        Passport::actingAs($this->employeeUser);

        $response = $this->getJson('/api/v1/employer/jobs', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employeeUser->createToken('TestToken')->accessToken,
            'title' => 'Software Developer',
            'company_name' => 'Tech Solutions',
            'location' => 'New York',
            'description' => 'Job description goes here',
            'instruction' => 'Instructions for applicants',
            'deadline' => now()->addDays(10)->toDateString(),
            'keywords' => 'PHP, Laravel, Developer',
        ]);
        // Assert the response
        $response->assertStatus(401);
    }
}
