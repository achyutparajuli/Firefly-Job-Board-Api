<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'skills'
    ];
}
