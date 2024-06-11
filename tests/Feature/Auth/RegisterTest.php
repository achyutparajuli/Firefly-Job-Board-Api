<?php

namespace Tests\Feature\Auth;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Mail\VerifyUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success()
    {
        Mail::fake();

        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'mobile' => '1234567890',
            'password' => 'password123',
            'user_type' => 'employee',
            'linkedin_profile' => 'https://www.linkedin.com/in/testuser',
            'gender' => 'male',
            'bio' => 'This is a bio.',
            'job_title' => 'Developer',
        ];

        $response = $this->postJson('/api/v1/register', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered succesfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'mobile' => '1234567890',
        ]);

        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));

        Mail::assertQueued(VerifyUser::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_register_validation_errors()
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'mobile' => 'invalid-mobile',
            'password' => 'short',
            'user_type' => 'invalid-type',
            'linkedin_profile' => '',
            'gender' => '',
            'bio' => '',
            'job_title' => '',
        ];

        $response = $this->postJson('/api/v1/register', $data);

        // dd($response);
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'mobile',
                'password',
                'user_type',
                'linkedin_profile',
                'gender',
                'bio',
                'job_title',
            ]);
    }

    public function test_register_duplication()
    {
        Mail::fake();

        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'mobile' => '1234567890',
            'password' => 'password123',
            'user_type' => 'employee',
            'linkedin_profile' => 'https://www.linkedin.com/in/testuser',
            'gender' => 'male',
            'bio' => 'This is a bio.',
            'job_title' => 'Developer',
        ];

        $response = $this->postJson('/api/v1/register', $data);

        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'mobile' => '1234567890',
            'password' => 'password123',
            'user_type' => 'employee',
            'linkedin_profile' => 'https://www.linkedin.com/in/testuser',
            'gender' => 'male',
            'bio' => 'This is a bio.',
            'job_title' => 'Developer',
        ];

        $response = $this->postJson('/api/v1/register', $data);

        $response->assertStatus(422); // the details are already taken
    }
}
