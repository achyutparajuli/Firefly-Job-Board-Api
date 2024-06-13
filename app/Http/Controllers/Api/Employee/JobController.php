<?php

namespace App\Http\Controllers\Api\Employee;

use Exception;
use Carbon\Carbon;
use App\Models\Job;
use Illuminate\Support\Str;
use App\Mail\NewApplication;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\SendResponseController;

class JobController extends SendResponseController
{
    public function index(Request $request)
    {
        try
        {
            $data = $request->all();
            $jobs = Job::fetchJobsQuery($data)->get();

            return $this->sendSuccess($jobs, 'All Jobs List', 200);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function applications(Request $request)
    {
        try
        {
            $jobs = JobApplication::select('job_listings.*', 'job_applications.*')
                ->join('job_listings', 'job_applications.job_id', 'job_listings.id')
                ->where('job_applications.employee_id', Auth::User()->id)
                ->where('job_applications.status', 'pending')
                ->get();
            return $this->sendSuccess($jobs, 'All Active Job Applications', 200);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function apply(Request $request)
    {
        $slug = $request->slug;
        try
        {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'slug' => 'required|string',
                'experience' => 'required|string',
                'skills' => 'required|string',
                'cv' => 'required|file|mimes:pdf',
                'cover_letter_file' => 'required_without:cover_letter_content|file|mimes:pdf|nullable',
                'cover_letter_content' => 'required_without:cover_letter_file|string|nullable',
            ]);

            if ($validator->fails())
            {
                return $this->sendError($validator->errors(), 422);
            }

            $job = Job::select('users.name as employer_name', 'users.email as employer_email', 'job_listings.*')
                ->where('job_listings.slug', $slug)
                ->join('users', 'job_listings.employer_id', 'users.id')
                ->first();

            if (!$job)
            {
                return $this->sendError('This job is not found! Please try again.');
            }

            if (!$job->status)
            {
                return $this->sendError('This job is not active! Please try again.');
            }

            if ($job->deadline && Carbon::parse($job->deadline)->isPast())
            {
                return $this->sendError('This job is closed! Please try other jobs.');
            }

            // check if already applied
            $checkIfAlreadyApplied = JobApplication::where('job_id', $job->id)
                ->where('employee_id', Auth::User()->id)
                ->first();

            if ($checkIfAlreadyApplied)
            {
                return $this->sendError('You have already applied for this job! Please try other jobs.');
            }

            $cover_letter_file = NULL;
            if ($request->cover_letter_file)
            {
                if ($request->hasFile('cover_letter_file'))
                {
                    $cover_letter_file = $request->file('cover_letter_file')->store('cover_letter_file', 'public');
                }
            }

            $cv = NULL;
            if ($request->cv)
            {
                if ($request->hasFile('cv'))
                {
                    $cv = $request->file('cv')->store('cv', 'public');
                }
            }

            // create new entry for job application
            $jobApplication = JobApplication::create([
                'job_id' => $job->id,
                'slug' => Str::uuid(),
                'employee_id' => Auth::User()->id,
                'cv' => $cv,
                'cover_letter_file' => $cover_letter_file,
                'cover_letter_content' => $request->cover_letter_content,
                'experience' => $request->experience,
                'skills' => $request->skills,
            ]);

            Mail::to($job->employer_email)
                ->queue(new NewApplication($jobApplication, $job));
            // added queue so that the api response doenst take longer time.

            DB::commit();
            return $this->sendSuccess($request->all(), 'Job Application sent succesfully.', 200);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
