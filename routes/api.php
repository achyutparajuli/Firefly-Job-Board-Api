<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Employee\JobController as EmployeeJobController;
use App\Http\Controllers\Api\Employeer\JobController as EmployeerJobController;
use App\Http\Controllers\Api\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'v1'], function () {
    // Register route
    Route::post('/register', [RegisterController::class, 'register']);

    // Login route
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        // Logout route for both type of users.
        Route::post('/logout', [LoginController::class, 'logout']);


        // routes for EMPLOYEER
        Route::group(['prefix' => 'employeer', 'middleware' => ['employeer']], function () {
            // Register route
            Route::post('/jobs', [EmployeerJobController::class, 'store']);
            Route::get('/jobs', [EmployeerJobController::class, 'index']);
            Route::put('/jobs/update/{slug}', [EmployeerJobController::class, 'update']);
            Route::delete('/jobs/delete/{slug}', [EmployeerJobController::class, 'delete']);
            Route::post('/jobs/status', [EmployeerJobController::class, 'changeStatus']);
        });

        // routes for EMPLOYEE
        Route::group(['prefix' => 'employee', 'middleware' => ['employee']], function () {
            // Register route
            Route::get('/jobs', [EmployeeJobController::class, 'index']);
            Route::get('/active-applications', [EmployeeJobController::class, 'applications']);
            Route::post('/jobs/apply', [EmployeeJobController::class, 'apply']);
        });
    });

    // this doesnt requires any login and is for guest users to, they have to login to apply/post
    Route::get('/all-jobs', [EmployeeJobController::class, 'index']);
});
