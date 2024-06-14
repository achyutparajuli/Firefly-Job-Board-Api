<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    protected $table = 'job_listings';

    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'company_name',
        'location',
        'description',
        'instruction',
        'deadline',
        'salary',
        'employer_id',
        'employee_id',
        'instruction',
        'slug',
        'keywords',
    ];

    public function application()
    {
        return $this->hasMany(JobApplication::class);
    }

    public static function fetchJobsQuery($data)
    {
        $keywords = $data['keywords'] ?? NULL;
        $companyName = $data['company_name'] ?? NULL;
        $location = $data['location'] ?? NULL;
        $authUserId = $data['authUserId'] ?? NULL;
        $slug = $data['slug'] ?? NULL;

        $jobs = Job::select('job_listings.*', 'employer.email as employer_email')
            ->addSelect(DB::raw('IF(deadline < NOW(), 1, 0) as expired'))
            ->where(function ($query) use ($keywords)
            {
                if ($keywords != '')
                {
                    $query->where('job_listings.title', 'LIKE', '%' . $keywords . '%');
                    $query->orwhere('job_listings.keywords', 'LIKE', '%' . $keywords . '%');
                }
            })
            ->where(function ($query) use ($location)
            {
                if ($location != '')
                {
                    $query->where('job_listings.location', 'LIKE', '%' . $location . '%');
                }
            })
            ->where(function ($query) use ($authUserId)
            {
                if ($authUserId != '')
                {
                    $query->where('job_listings.employer_id', $authUserId);
                }
            })
            ->where(function ($query) use ($companyName)
            {
                if ($companyName != '')
                {
                    $query->where('job_listings.company_name', 'LIKE', '%' . $companyName . '%');
                }
            })
            ->where(function ($query) use ($slug)
            {
                if ($slug != '')
                {
                    $query->where('job_listings.slug', $slug);
                }
            })
            ->where('job_listings.status', 1)
            ->join('users as employer', 'job_listings.employer_id', 'employer.id')
            ->withCount('application as total_applications')
            ->orderBy('id', 'DESC');

        return $jobs;
    }

    public static function isJobClosed($job)
    {
        return $job->deadline && Carbon::parse($job->deadline)->isPast();
    }

    public static function hasAlreadyApplied($jobId)
    {
        return JobApplication::where('job_id', $jobId)
            ->where('employee_id', Auth::User()->id)
            ->count();
    }

    public static function storeFile(Request $request, $fileKey, $storagePath)
    {
        if ($request->hasFile($fileKey))
        {
            return $request->file($fileKey)->store($storagePath, 'public');
        }
        return null;
    }
}
