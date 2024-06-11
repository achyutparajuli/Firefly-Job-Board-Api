<?php

namespace Tests\Feature\Employee;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\JobApplication;
use Laravel\Passport\Passport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplyJobTest extends TestCase
{
    public $employeeUser, $job;
    /**
     * A basic feature test example.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Run the Passport install command programmatically
        Artisan::call('passport:install');

        $num = rand(1, 99999);
        $this->employeeUser = User::create([
            'id' => $num,
            'email' => 'test' . $num . '@example.com',
            'name' => 'name',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
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
            'employer_id' => $this->employeeUser->id
        ]);
    }

    public function test_apply_applications_without_token()
    {
        $response = $this->postJson('/api/v1/employee/jobs/apply', []);
        $response->assertStatus(401);
    }

    public function test_apply_application_with_token()
    {
        // Authenticate the user
        Passport::actingAs($this->employeeUser);

        $response = $this->postJson('/api/v1/employee/jobs/apply', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->employeeUser->createToken('TestToken')->accessToken,
            'slug' => $this->job->slug,
            'experience' => 'Some experience',
            'skills' => 'Some skills',
            'cv' => UploadedFile::fake()->create('cv.pdf'),
            'cover_letter_file' => UploadedFile::fake()->create('cover_letter.pdf'),
        ]);

        // Assert the response
        $response->assertStatus(200);
    }
}
