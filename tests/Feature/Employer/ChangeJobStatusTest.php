<?php

namespace Tests\Feature\Employer;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\JobApplication;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Artisan;

class ChangeJobStatusTest extends TestCase
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

    public function test_update_my_jobs_without_token()
    {
        $response = $this->postJson('/api/v1/employer/jobs/status', []);
        $response->assertStatus(401);
    }

    public function test_update_my_jobs_with_proper_token()
    {
        $jobApplication = JobApplication::create([
            'job_id' => $this->job->id,
            'cv' => 'cv',
            'slug' => Str::uuid(),
            'employee_id' => $this->employeeUser->id,
            'status' => 'pending'
        ]);


        // Authenticate the user
        Passport::actingAs($this->employerUser);

        $response = $this->postJson('/api/v1/employer/jobs/status', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employerUser->createToken('TestToken')->accessToken,
            'slug' => $jobApplication->slug,
            'status' => 'approved',
            'remarks' => 'Application approved',
        ]);

        // Assert the response
        $response->assertStatus(200);
    }

    public function test_update_my_jobs_with__valid_token_with_incomplete_inputs()
    {
        // Authenticate the user
        Passport::actingAs($this->employerUser);

        $response = $this->postJson('/api/v1/employer/jobs/status', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employerUser->createToken('TestToken')->accessToken,
            'slug' => $this->job->slug,
        ]);

        // Assert the response
        $response->assertStatus(422);
    }

    public function test_update_my_jobs_with_valid_token_with_wrong_user_role()
    {
        Passport::actingAs($this->employeeUser);

        $response = $this->postJson('/api/v1/employer/jobs/status', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employeeUser->createToken('TestToken')->accessToken,

            'slug' => $this->job->slug,
            'status' => 'approved',
            'remarks' => 'Application approved',
        ]);

        // Assert the response
        $response->assertStatus(401);
    }
}
