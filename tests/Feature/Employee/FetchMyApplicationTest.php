<?php

namespace Tests\Feature\Employee;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\JobApplication;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FetchMyApplicationTest extends TestCase
{
    public $user, $job1, $job2;
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
            'user_type' => 'employee'
        ]);

        // Create some job listings
        $this->job1 = Job::create([
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

        $this->job2 = Job::create([
            'title' => 'Software Engineer 2',
            'keywords' => 'PHP, Laravel 2',
            'slug' => Str::uuid(),
            'location' => 'Kathmandu  2',
            'company_name' => 'Tech Company 2',
            'status' => 1,
            'deadline' => now()->addDays(10),
            'description' => 'desc2',
            'instruction' => 'instruction2',
            'employer_id' => $this->user->id
        ]);

        // Create job applications
        JobApplication::create([
            'job_id' => $this->job1->id,
            'cv' => 'cv',
            'slug' => Str::uuid(),
            'employee_id' => $this->user->id,
            'status' => 'pending'
        ]);

        JobApplication::create([
            'job_id' => $this->job2->id,
            'cv' => 'cv',
            'slug' => Str::uuid(),
            'employee_id' => $this->user->id,
            'status' => 'approved'
        ]);
    }

    public function test_fetch_applications_without_token()
    {
        $response = $this->getJson('/api/v1/employee/active-applications');
        $response->assertStatus(401);
    }

    public function test_fetch_applications_with_correct_token()
    {
        // Authenticate the user
        Passport::actingAs($this->user);

        // Send the request
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->user->createToken('TestToken')->accessToken,
        ])->getJson('/api/v1/employee/active-applications');

        // Verify the response
        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'pending']);
    }
}
