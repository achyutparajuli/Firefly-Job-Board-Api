<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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
        $authUserId = $data['authUserId'];

        $jobs = Job::select('job_listings.*')
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
            ->where('job_listings.status', 1)
            ->withCount('application as total_applications')
            ->orderBy('id', 'DESC');

        return $jobs;
    }
}
