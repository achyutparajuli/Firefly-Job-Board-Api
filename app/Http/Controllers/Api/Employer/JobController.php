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
        $keyword = $request->keyword; // we can use this seperately.

        try {
            $jobs = Job::select('job_listings.*')
                ->addSelect(DB::raw('IF(deadline < NOW(), 1, 0) as expired'))
                ->where(function ($query) use ($keyword) {
                    if ($keyword != '') {
                        $query->where('job_listings.title', 'LIKE', '%' . $keyword . '%');
                        $query->orwhere('job_listings.company_name', 'LIKE', '%' . $keyword . '%');
                        $query->orwhere('job_listings.location', 'LIKE', '%' . $keyword . '%');
                    }
                })
                ->where('employer_id', Auth::User()->id)
                ->withCount('application as total_applications')
                ->orderBy('id', 'DESC')
                ->get();
            return $this->sendSuccess($jobs, 'My Jobs List', 200);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
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

            if ($validator->fails()) {
                return $this->sendError($validator->errors(), 422);
            }

            $job = Job::create([
                'slug' => Str::uuid(),
                'title' => $request->title,
                'company_name' => $request->company_name,
                'location' => $request->location,
                'description' => $request->description,
                'instruction' => $request->instruction,
                'deadline' => $request->deadline,
                'salary' => $request->salary,
                'keywords' => $request->keywords,
                'employer_id' => Auth::User()->id
            ]);

            return $this->sendSuccess($request->all(), 'Job created succesfully', 201);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function update($slug, Request $request)
    {
        try {
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

            if ($validator->fails()) {
                return $this->sendError($validator->errors(), 422);
            }

            $job = Job::query()
                ->where('slug', $slug)
                ->where('employer_id', Auth::User()->id)
                ->first();


            if (!$job) {
                return $this->sendError('This job doesn`t exists! Please try again.');
            }

            $job->update([
                'title' => $request->title,
                'company_name' => $request->company_name,
                'location' => $request->location,
                'description' => $request->description,
                'instruction' => $request->instruction,
                'keywords' => $request->keywords,
                'deadline' => $request->deadline,
                'salary' => $request->salary,
            ]);

            return $this->sendSuccess($request->all(), 'Job updated Sucesfully', 200);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function delete($slug)
    {
        try {
            $job = Job::where('slug', $slug)->where('employer_id', Auth::User()->id)->first();

            if (!$job) {
                return $this->sendError('This job is not found! Please try again.');
            }

            $job->delete();
            return $this->sendSuccess($job, 'Job Deleted Succesfully', 200);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function changeStatus(Request $request)
    {
        $slug = $request->slug;
        $remarks = $request->remarks;
        $status = $request->status; // status to be changed
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'slug' => 'required|string',
                'status' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors(), 422);
            }

            $job = JobApplication::select('job_listings.*', 'employee.name as employee_name', 'employee.email as employee_email')
                ->where('employer_id', Auth::User()->id)
                ->where('job_applications.slug', $slug)
                ->join('job_listings', 'job_applications.job_id', 'job_listings.id')
                ->join('users as employee', 'job_applications.employee_id', 'employee.id')
                ->first();

            if (!$job) {
                return $this->sendError('This job is not found! Please try again.');
            }

            JobApplication::where('slug', $slug)
                ->update([
                    'status' => $status,
                    'remarks' => $remarks
                ]);

            $when = now()->addMinutes(1);

            Mail::to($job->employee_email)
                ->queue(new ApplicationStatus($job));

            DB::commit();
            return $this->sendSuccess($request->all(), 'Job updated succesfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
