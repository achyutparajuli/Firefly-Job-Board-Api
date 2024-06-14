<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplication extends Model
{
    use HasFactory;

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    protected $fillable = [
        'job_id',
        'slug',
        'employee_id',
        'cv',
        'cover_letter_file',
        'cover_letter_content',
        'experience',
        'skills',
        'remarks',
        'status'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public static function fetchJobsApplicationQuery($data)
    {
        $employeeId = $data['employee_id'] ?? NULL;
        $employerId = $data['employer_id'] ?? NULL;
        $applicationStatus = $data['status'] ?? 'pending';

        $jobApplications = JobApplication::select('job_listings.*', 'job_applications.*')
            ->join('job_listings', 'job_applications.job_id', 'job_listings.id')
            ->where(function ($query) use ($employeeId)
            {
                if ($employeeId != '')
                {
                    $query->where('job_applications.employee_id', $employeeId);
                }
            })
            ->where(function ($query) use ($employerId)
            {
                if ($employerId != '')
                {
                    $query->where('job_listings.employer_id', $employerId);
                }
            })
            ->where(function ($query) use ($applicationStatus)
            {
                if ($applicationStatus != '')
                {
                    $query->where('job_applications.status', $applicationStatus);
                }
            })
            ->orderBy('job_applications.id', 'DESC');

        return $jobApplications;
    }
}
