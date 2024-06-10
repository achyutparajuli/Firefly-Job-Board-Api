<?php

namespace App\Http\Controllers\Api\Employee;

use Exception;
use Carbon\Carbon;
use App\Models\Job;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\SendResponseController;

class JobController extends SendResponseController
{
    public function index(Request $request)
    {
        $keywords = $request->keywords;
        $companyName = $request->company_name;
        $location = $request->location;
        try {
            $jobs = Job::select('job_listings.*')
                ->addSelect(DB::raw('IF(deadline < NOW(), 1, 0) as expired'))
                ->where(function ($query) use ($keywords) {
                    if ($keywords != '') {
                        $query->where('job_listings.title', 'LIKE', '%' . $keywords . '%');
                        $query->orwhere('job_listings.keywords', 'LIKE', '%' . $keywords . '%');
                    }
                })
                ->where(function ($query) use ($location) {
                    if ($location != '') {
                        $query->where('job_listings.location', 'LIKE', '%' . $location . '%');
                    }
                })
                ->where(function ($query) use ($companyName) {
                    if ($companyName != '') {
                        $query->where('job_listings.company_name', 'LIKE', '%' . $companyName . '%');
                    }
                })
                ->withCount('application as total_applications')
                ->get();
            return $this->sendSuccess($jobs, 'All Jobs List', 200);
        } catch (Exception $e) {
            return $e->getMessage();
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function applications(Request $request)
    {
        try {
            $jobs = JobApplication::select('job_listings.*', 'job_applications.*')
                ->join('job_listings', 'job_applications.job_id', 'job_listings.id')
                ->where('job_applications.employee_id', Auth::User()->id)
                ->where('job_applications.status', 'pending')
                // for all active submissions, rejected and approved are not active since the action is already done
                ->get();
            return $this->sendSuccess($jobs, 'All Active Job Applications', 200);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function apply(Request $request)
    {
        $slug = $request->slug;
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'slug' => 'required|string',
                'experience' => 'required|string',
                'skills' => 'required|string',
                'cv' => 'required|file|mimes:pdf',
                'cover_letter_file' => 'required_without:cover_letter_content|file|mimes:pdf|nullable',
                'cover_letter_content' => 'required_without:cover_letter_file|string|nullable',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            $job = Job::query()
                ->where('job_listings.slug', $slug)
                ->first();

            if (!$job) {
                return $this->sendError('This job is not found! Please try again.');
            }

            if (Carbon::parse($job->deadline)->isPast()) {
                return $this->sendError('This job is closed! Please try other jobs.');
            }

            // check if already applied
            $checkIfAlreadyApplied = JobApplication::where('job_id', $job->id)
                ->where('employee_id', Auth::User()->id)
                ->first();

            if ($checkIfAlreadyApplied) {
                return $this->sendError('You have already applied for this job! Please try other jobs.');
            }

            $cover_letter_file = NULL;
            if ($request->cover_letter_file) {
                if ($request->hasFile('cover_letter_file')) {
                    $cover_letter_file = $request->file('cover_letter_file')->store('cover_letter_file', 'public');
                }
            }

            $cv = NULL;
            if ($request->cv) {
                if ($request->hasFile('cv')) {
                    $cv = $request->file('cv')->store('cv', 'public');
                }
            }

            // create new entry for job application
            $user = JobApplication::create([
                'job_id' => $job->id,
                'slug' => Str::uuid(),
                'employee_id' => Auth::User()->id,
                'cv' => $cv,
                'cover_letter_file' => $cover_letter_file,
                'cover_letter_content' => $request->cover_letter_content,
                'experience' => $request->experience,
                'skills' => $request->skills,
            ]);

            // send email to associated emploeer. trigger email

            DB::commit();
            return $this->sendSuccess($job, 'Job Application sent succesfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
