<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // seeder for employe & employeer based on user_type
        User::create([
            'id' => 1,
            'name' => 'Achyut Achyut',
            'email' => 'achyut@gmail.com',
            'password' => Hash::make('password'),
            'status' => true,
            'user_type' => 'employeer',
            'job_title' => 'HR Manager',
            'mobile' => '9846150836',
            'email_verified_at' => '2024-06-06 17:14:43', // adding these because user has to verify their email to login.
            'api_token' => null,
        ]);

        User::create([
            'id' => 2,
            'name' => 'parajuli parajuli',
            'email' => 'parajuli@gmail.com',
            'password' => Hash::make('password'),
            'status' => true,
            'user_type' => 'employee',
            'job_title' => 'LAMP Developer',
            'mobile' => '9827188090',
            'email_verified_at' => '2024-06-06 17:14:43', // adding these because user has to verify their email to login.
            'api_token' => null,
        ]);
    }
}
