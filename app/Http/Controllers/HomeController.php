<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index($token = null)
    {
        $message = NULL;
        // we can implement the logic that: the link is valid for vertain minute only
        if ($token) {
            $user = User::where('verify_token', $token)->firstOrFail();
            $user->status = 1;
            $user->email_verified_at = Carbon::now();
            $user->verify_token = NULL;
            $user->save();
            $message = "Email verified";
        }

        $features = [
            [
                "heading" => "Extensive Job Listings",
                "description" => "Explore a wide range of job listings across various industries and locations. Find the job that fits your skills and preferences."
            ],
            [
                "heading" => "Easy Application Process",
                "description" => "Apply for jobs with a single click. Our streamlined application process ensures that your applications are quickly submitted."
            ],
            [
                "heading" => "Job Alerts",
                "description" => "Stay updated with the latest job openings. Set up job alerts and receive notifications when new jobs that match your criteria are posted."
            ],
            [
                "heading" => "Resume Builder",
                "description" => "Create a professional resume using our easy-to-use resume builder. Stand out to potential employers with a polished and complete resume."
            ]
        ];

        /* 
            Returning to view with the feature array variable so that we dont have to write it statically on the blade page.
            Assumtion: In future we will fetch the features from the backend rather that hardcoding it.
        */
        return view('web.index', compact('features', 'message'));
    }
}
