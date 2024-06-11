<?php

namespace Tests\Feature;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Artisan;

class GetJobsListTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $num = rand(1, 99999);
        $user = User::create([
            'id' => $num,
            'email' => 'test' . $num . '@example.com',
            'name' => 'name',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev'
        ]);

        // Create some job listings
        Job::create([
            'title' => 'Software Engineer',
            'keywords' => 'PHP, Laravel',
            'slug' => Str::uuid(),
            'location' => 'Kathmandu',
            'company_name' => 'Tech Company',
            'status' => 1,
            'deadline' => now()->addDays(10),
            'description' => 'desc',
            'instruction' => 'instruction',
            'employer_id' => $user->id
        ]);

        Job::create([
            'title' => 'Web Developer',
            'keywords' => 'HTML, CSS',
            'slug' => Str::uuid(),
            'location' => 'Pokhara',
            'company_name' => 'Web Solutions',
            'status' => 1,
            'deadline' => now()->addDays(5),
            'description' => 'desc',
            'instruction' => 'instruction',
            'employer_id' => $user->id
        ]);

        Job::create([
            'title' => 'Project Manager',
            'keywords' => 'Management, Agile',
            'slug' => Str::uuid(),
            'location' => 'Lalitpur',
            'company_name' => 'Management Inc.',
            'status' => 1,
            'deadline' => now()->subDays(2), // This job is expired
            'description' => 'desc',
            'instruction' => 'instruction',
            'employer_id' => $user->id
        ]);
    }

    public function test_get_all_jobs_without_token()
    {
        $response = $this->getJson('/api/v1/employee/jobs');
        $response->assertStatus(401);
    }

    public function test_get_all_jobs_with_valid_token()
    {
        // Run the Passport install command programmatically
        Artisan::call('passport:install');

        // Create a user
        $num = rand(1, 99999);
        // Create a user
        $user = User::create([
            'email' => 'test' . $num . '@example.com',
            'name' => 'asd',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev',
            'user_type' => 'employee'
        ]);

        // Generate a token for the user
        Passport::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken($user->email)->accessToken,
        ])->getJson('/api/v1/employee/jobs');

        $response->assertStatus(200);
    }


    public function test_get_jobs_with_all_available_filters()
    {
        // Create a user
        $num = rand(1, 99999);
        // Create a user
        $user = User::create([
            'email' => 'test' . $num . '@example.com',
            'name' => 'asd',
            'password' => bcrypt('password'),
            'mobile' => '98' . $num . '1',
            'job_title' => 'laravel dev',
            'user_type' => 'employee'
        ]);

        // Generate a token for the user
        Passport::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken($user->email)->accessToken,
        ])->getJson('/api/v1/employee/jobs?keywords=PHP&location=Kathmandu&company_name=Tech Company');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Software Engineer'])
            ->assertJsonMissing(['title' => 'Web Developer']);
    }
}
