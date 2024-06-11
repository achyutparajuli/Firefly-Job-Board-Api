<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use App\Mail\VerifyUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\SendResponseController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegisterController extends SendResponseController
{
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:70'],
                'email' => ['required', 'string', 'email', 'max:100', 'unique:users'],
                'mobile' => ['required', 'digits:10', 'unique:users'],
                'password' => ['required', 'string', 'min:6'],
                'user_type' => ['required', 'string', 'in:employee,employer'],
                'linkedin_profile' => ['required', 'string'],
                'gender' => ['required', 'string'],
                'bio' => ['required', 'string'],
                'job_title' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors(), 422);
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

            $verifyToken = Str::uuid();
            $user->update([
                'verify_token' => $verifyToken,
                'token_sent_at' => Carbon::now()
            ]);

            Mail::to($user->email)
                ->queue(new VerifyUser($user));

            DB::commit();
            return $this->sendSuccess($request->all(), 'User registered succesfully', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
