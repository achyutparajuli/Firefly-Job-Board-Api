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
            $jobs = Job::fetchJobsQuery($request->all())->get();

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
            $request->merge(['employee_id' => Auth::User()->id]);
            $jobApplication = JobApplication::fetchJobsApplicationQuery($request->all())->get();

            return $this->sendSuccess($jobApplication, 'All Active Job Applications', 200);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function apply(Request $request)
    {
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

        try
        {
            DB::beginTransaction();

            $job = Job::fetchJobsQuery($request->all())->first();

            if (!$job)
            {
                return $this->sendError('This job is not found! Please try again.', 404);
            }

            if (Job::isJobClosed($job))
            {
                return $this->sendError('This job is closed! Please try other jobs.', 400);
            }

            if (Job::hasAlreadyApplied($job->id))
            {
                return $this->sendError('You have already applied for this job! Please try other jobs.', 400);
            }

            $validatedData = $validator->validated();
            $validatedData['job_id'] = $job->id;
            $validatedData['slug'] = Str::uuid();
            $validatedData['employee_id'] = Auth::id();
            $validatedData['cv'] = Job::storeFile($request, 'cv', 'cv');

            if ($request->hasFile('cover_letter_file'))
            {
                $validatedData['cover_letter_file'] = Job::storeFile($request, 'cover_letter_file', 'cover_letter_file');
            }

            // Create new entry for job application
            $jobApplication = JobApplication::create($validatedData);

            // Queue the email so that the API response doesn't take longer time.
            Mail::to($job->employer_email)
                ->queue(new NewApplication($jobApplication, $job));

            DB::commit();

            return $this->sendSuccess($validatedData, 'Job Application sent successfully.', 200);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            return $this->sendError('Error: Something went wrong! Please try again.', 500);
        }
    }
}
