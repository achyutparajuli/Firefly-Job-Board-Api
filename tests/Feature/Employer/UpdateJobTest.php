<?php

namespace Tests\Feature\Employer;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateJobTest extends TestCase
{
    public $user, $user1, $job;
    /**
     * A basic feature test example.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Run the Passport install command programmatically
        Artisan::call('passport:install');

        $num = rand(1, 99999);
        $this->user = User::create([
            'id' => $num,
            'email' => 'test' . $num . '@example.com',
            'name' => 'name',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev',
            'user_type' => 'employer'
        ]);

        $num1 = rand(1, 99999);
        $this->user1 = User::create([
            'id' => $num1,
            'email' => 'test' . $num1 . '@example.com',
            'name' => 'name',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num1 . '1',
            'job_title' => 'laravel dev',
            'user_type' => 'employee'
        ]);

        // Create some job listings
        $this->job = Job::create([
            'title' => 'Software Engineer',
            'keywords' => 'PHP, Laravel',
            'slug' => Str::uuid(),
            'location' => 'Kathmandu',
            'company_name' => 'Tech Company',
            'status' => 1,
            'deadline' => now()->addDays(10),
            'description' => 'desc',
            'instruction' => 'instruction',
            'employer_id' => $this->user->id
        ]);
    }

    public function test_update_my_jobs_without_token()
    {
        $response = $this->putJson('/api/v1/employer/jobs/update/' . $this->job->slug, []);
        $response->assertStatus(401);
    }

    public function test_update_my_jobs_with_valid_token()
    {
        // Authenticate the user
        Passport::actingAs($this->user);

        $response = $this->putJson('/api/v1/employer/jobs/update/' . $this->job->slug, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->user->createToken('TestToken')->accessToken, 'title' => 'Software Developer',
            'company_name' => 'ccc',
            'title' => 'ttt',
            'location' => 'llll',
            'description' => 'Job description goes here',
            'instruction' => 'Instructions for applicants',
            'deadline' => now()->addDays(10)->toDateString(),
            'keywords' => 'PHP, Laravel, Developer',
        ]);
        // Assert the response
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'job_listings',
            [
                'title' => 'ttt',
                'company_name' => 'ccc',
                'location' => 'llll',
            ]
        );
    }

    public function test_update_my_jobs_with_valid_token_with_incomplete_input()
    {
        // Authenticate the user
        Passport::actingAs($this->user);

        $response = $this->putJson('/api/v1/employer/jobs/update/' . $this->job->slug, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->user->createToken('TestToken')->accessToken, 'title' => 'Software Developer',
        ]);

        // Assert the response
        $response->assertStatus(422);
    }

    public function test_update_my_jobs_with_valid_token_with_wrong_user_role()
    {
        Passport::actingAs($this->user1);

        $response = $this->putJson('/api/v1/employer/jobs/update/' . $this->job->slug, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->user1->createToken('TestToken')->accessToken, 'title' => 'Software Developer',
            'company_name' => 'ccc',
            'title' => 'ttt',
            'location' => 'llll',
            'description' => 'Job description goes here',
            'instruction' => 'Instructions for applicants',
            'deadline' => now()->addDays(10)->toDateString(),
            'keywords' => 'PHP, Laravel, Developer',
        ]);

        // Assert the response
        $response->assertStatus(401);
    }
}
