<?php

namespace App\Http\Controllers\Api\Employeer;

use Exception;
use App\Models\Job;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\SendResponseController;

class JobController extends SendResponseController
{
    public function index(Request $request)
    {
        $keyword = $request->keyword;
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
                ->where('employeer_id', Auth::User()->id)
                ->withCount('application as total_applications')
                ->get();
            return $this->sendSuccess($jobs, 'My Jobs List', 201);
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
                return $this->sendError($validator->errors());
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
                'employeer_id' => Auth::User()->id
            ]);

            return $this->sendSuccess($request->all(), 'Job created succesfully', 201);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function update($slug, Request $request)
    {
        $keyword = $request->keyword;
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
                return $this->sendError($validator->errors());
            }

            $job = Job::query()
                ->where('slug', $slug)
                ->where('employeer_id', Auth::User()->id)
                ->first();

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

            if (!$job) {
                return $this->sendError('This job doesn`t exists! Please try again.');
            }
            return $this->sendSuccess($request->all(), 'Job updated Sucesfully', 200);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function delete($slug)
    {
        try {
            $job = Job::where('employeer_id', Auth::User()->id)->first();
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
        $status = $request->status;
        try {
            DB::beginTransaction();

            $job = JobApplication::select('job_listings.*')
                ->where('employeer_id', Auth::User()->id)
                ->where('job_applications.slug', $slug)
                ->join('jobs', 'job_applications.job_id', 'job_listings.id')
                ->first();

            if (!$job) {
                return $this->sendError('This job is not found! Please try again.');
            }

            $job->status = $status;
            $job->remarks = $remarks;
            $job->save();

            // trigger an email
            DB::commit();

            return $this->sendSuccess($job, 'Job updated succesfully.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
