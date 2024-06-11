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

class DeleteMyJobTest extends TestCase
{
    public $employerUser, $employeeUser, $job;
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
            'employer_id' => $this->employerUser->id
        ]);
    }

    public function test_delete_my_jobs_without_token()
    {
        $response = $this->deleteJson('/api/v1/employer/jobs/delete/' . $this->job->slug, []);
        $response->assertStatus(401);
    }

    public function test_delete_my_jobs_with_valid_token()
    {
        // Authenticate the user
        Passport::actingAs($this->employerUser);

        $response = $this->deleteJson('/api/v1/employer/jobs/delete/' . $this->job->slug, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employerUser->createToken('TestToken')->accessToken,
        ]);

        // Assert the response
        $response->assertStatus(200);
    }

    public function test_delete_my_jobs_with_valid_token_with_wrong_user_role()
    {
        // Authenticate the user
        Passport::actingAs($this->employeeUser);

        $response = $this->deleteJson('/api/v1/employer/jobs/delete/' . $this->job->slug, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employeeUser->createToken('TestToken')->accessToken,
        ]);
        // Assert the response
        $response->assertStatus(401);
    }
}
