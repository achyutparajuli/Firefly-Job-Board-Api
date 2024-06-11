<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Employee\JobController as EmployeeJobController;
use App\Http\Controllers\Api\Employer\JobController as EmployerJobController;
use App\Http\Controllers\Api\Auth\RegisterController;


Route::group(['prefix' => 'v1'], function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']); // common for both user types.


        // routes for EMPLOYER
        Route::group(['prefix' => 'employer', 'middleware' => ['employer']], function () {
            Route::post('/jobs', [EmployerJobController::class, 'store']); // create job
            Route::get('/jobs', [EmployerJobController::class, 'index']); // get my jobs
            Route::put('/jobs/update/{slug}', [EmployerJobController::class, 'update']); // update my job
            Route::delete('/jobs/delete/{slug}', [EmployerJobController::class, 'delete']); // delete my job
            Route::post('/jobs/status', [EmployerJobController::class, 'changeStatus']); // change application status
        });


        // routes for EMPLOYEE
        Route::group(['prefix' => 'employee', 'middleware' => ['employee']], function () {
            Route::get('/jobs', [EmployeeJobController::class, 'index']); // get list of all jobs
            Route::get('/active-applications', [EmployeeJobController::class, 'applications']); // get my active aplications
            Route::post('/jobs/apply', [EmployeeJobController::class, 'apply']); // apply for a job
        });
    });

    // this doesnt requires any login and is for guest users to, they have to login to apply/post
    Route::get('/all-jobs', [EmployeeJobController::class, 'index']);
});
