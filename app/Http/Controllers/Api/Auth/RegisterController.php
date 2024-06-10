<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\SendResponseController;

class RegisterController extends SendResponseController
{
    public function register(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:70'],
                'email' => ['required', 'string', 'email', 'max:100', 'unique:users'],
                'mobile' => ['required', 'digits:10', 'unique:users'],
                'password' => ['required', 'string', 'min:6'],
                'user_type' => ['required', 'string', 'in:employee,employeer'],
                'linkedin_profile' => ['required', 'string'],
                'gender' => ['required', 'string', 'in:male,female,other'],
                'bio' => ['required', 'string'],
                'job_title' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
                'job_title' => $request->job_title,
                'mobile' => $request->mobile,
                'status' => false, // By default the user is inactive, once they verify their email they will be active

                // We can add other fields as per required.
            ]);

            return $this->sendSuccess($request->all(), 'User registered succesfully', 201);
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
