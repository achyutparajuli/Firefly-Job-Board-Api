<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'job_listings';

    use HasFactory;

    protected $fillable = [
        'title',
        'company_name',
        'location',
        'description',
        'instruction',
        'deadline',
        'salary',
        'employeer_id',
        'employee_id',
        'instruction',
        'slug',
        'keywords'
    ];

    public function application()
    {
        return $this->hasMany(JobApplication::class);
    }
}
