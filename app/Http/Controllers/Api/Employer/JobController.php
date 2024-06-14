<?php

namespace App\Http\Controllers\Api\Employer;

use Exception;
use App\Models\Job;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Mail\ApplicationStatus;
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
            $request->merge(['authUserId' => Auth::User()->id]);
            $jobs = Job::fetchJobsQuery($request->all())->get();

            return $this->sendSuccess($jobs, 'My Jobs List', 200);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function store(Request $request)
    {
        try
        {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'title' => ['required', 'string', 'max:70'],
                'company_name' => ['required', 'string', 'max:100'],
                'location' => ['required', 'string', 'max:70'],
                'description' => ['required', 'string'],
                'instruction' => ['required', 'string'],
                'deadline' => ['nullable', 'date', 'after:today'],
                'keywords' => ['required', 'string'],
            ]);

            if ($validator->fails())
            {
                return $this->sendError($validator->errors(), 422);
            }

            // add other optional field value on the validated data array
            $validatedData = $validator->validated();
            $validatedData['slug'] = Str::uuid();
            $validatedData['employer_id'] = Auth::User()->id;
            $validatedData['salary'] = $request->salary;

            Job::create($validatedData);

            return $this->sendSuccess($request->all(), 'Job created succesfully', 201);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function update($slug, Request $request)
    {
        try
        {
            $job = Job::query()
                ->where('slug', $slug)
                ->where('employer_id', Auth::User()->id)
                ->first();

            if (!$job)
            {
                return $this->sendError('This job doesn`t exists! Please try again.');
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'title' => ['required', 'string', 'max:70'],
                'company_name' => ['required', 'string', 'max:100'],
                'location' => ['required', 'string', 'max:70'],
                'description' => ['required', 'string'],
                'instruction' => ['required', 'string'],
                'deadline' => ['nullable', 'date', 'after:today'],
                'keywords' =>  ['required', 'string'],
            ]);

            if ($validator->fails())
            {
                return $this->sendError($validator->errors(), 422);
            }

            // add other optional field value on the validated data array
            $validatedData = $validator->validated();
            $validatedData['salary'] = $request->salary;

            $job->update($validatedData);

            return $this->sendSuccess($request->all(), 'Job updated Sucesfully', 200);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function delete($slug)
    {
        try
        {
            $job = Job::where('slug', $slug)->where('employer_id', Auth::User()->id)->first();
            if (!$job)
            {
                return $this->sendError('This job is not found! Please try again.');
            }

            $job->delete();
            return $this->sendSuccess($job, 'Job Deleted Succesfully', 200);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function getJobApplications(Request $request)
    {
        try
        {
            $request->merge(['employer_id' => Auth::User()->id]);
            $jobApplication = JobApplication::fetchJobsApplicationQuery($request->all())->get();

            return $this->sendSuccess($jobApplication, 'My Job Applications List', 200);
        }
        catch (Exception $e)
        {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails())
        {
            return $this->sendError($validator->errors(), 422);
        }

        $slug = $request->slug;
        $status = $request->status;
        $remarks = $request->remarks;

        try
        {
            DB::beginTransaction();

            $jobApplication = JobApplication::where('slug', $slug)
                ->whereHas('job', function ($query)
                {
                    $query->where('employer_id', Auth::id());
                })
                ->with(['job', 'employee'])
                ->first();

            if (!$jobApplication)
            {
                return $this->sendError('This job application is not found! Please try again.', 404);
            }

            $jobApplication->update([
                'status' => $status,
                'remarks' => $remarks
            ]);

            $when = now()->addMinutes(env("EMAIL_DELAY", 10));

            Mail::to($jobApplication->employee->email)
                ->later($when, new ApplicationStatus($jobApplication));

            DB::commit();

            return $this->sendSuccess($jobApplication->toArray(), 'Job application status updated successfully.', 200);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            return $this->sendError('Error: Something went wrong! Please try again.', 500);
        }
    }
}
