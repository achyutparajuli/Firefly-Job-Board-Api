<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Employee\JobController as EmployeeJobController;
use App\Http\Controllers\Api\Employer\JobController as EmployerJobController;
use App\Http\Controllers\Api\Auth\RegisterController;


Route::group(['prefix' => 'v1'], function ()
{
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware('auth:api')->group(function ()
    {
        Route::post('/logout', [LoginController::class, 'logout']); // common for both user types.


        // routes for EMPLOYER
        Route::group(['prefix' => 'employer/jobs', 'middleware' => ['employer']], function ()
        {
            Route::post('/', [EmployerJobController::class, 'store']); // create job
            Route::get('/', [EmployerJobController::class, 'index']); // get my jobs
            Route::put('/update/{slug}', [EmployerJobController::class, 'update']); // update my job
            Route::delete('/delete/{slug}', [EmployerJobController::class, 'delete']); // delete my job
            Route::get('/applications', [EmployerJobController::class, 'getJobApplications']); // get the list of job applications for changing its status
            Route::post('/status', [EmployerJobController::class, 'changeStatus']); // change application status
        });


        // routes for EMPLOYEE
        Route::group(['prefix' => 'employee/jobs', 'middleware' => ['employee']], function ()
        {
            Route::get('/', [EmployeeJobController::class, 'index']); // get list of all jobs
            Route::get('/active-applications', [EmployeeJobController::class, 'applications']); // get my active aplications
            Route::post('/apply', [EmployeeJobController::class, 'apply']); // apply for a job
        });
    });

    // this doesnt requires any login and is for guest users to, they have to login to apply/post
    Route::get('/all-jobs', [EmployeeJobController::class, 'index']);
});
