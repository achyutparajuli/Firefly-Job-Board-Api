<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\SendResponseController;

class LoginController extends SendResponseController
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
                'email' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors(), 422);
            }

            $checkUser = User::where('email', $request->email)->first();
            if ($checkUser) {
                if (!$checkUser->email_verified_at) {
                    return $this->sendError('Invalid Login. Please verify your email.', 400);
                } else if (!$checkUser->status) {
                    return $this->sendError('Invalid Login. Please contact the system admin.', 400);
                }

                if (Hash::check($request->password, $checkUser->password)) {
                    // if login is success generate api token
                    $token = $checkUser->createToken($checkUser->email)->accessToken;

                    $result = [
                        'name' => $checkUser->name,
                        'id' => $checkUser->id,
                        'role' => $checkUser->role,
                        'email' => $checkUser->email,
                        'mobile' => $checkUser->mobile,
                        'profile_image' => $checkUser->profile_image,
                        'token' => $token,
                    ];

                    $checkUser->api_token = $token;
                    $checkUser->save();

                    return $this->sendSuccess($result, 'Login Succesfull.');
                } else {
                    return $this->sendError('The provided password is incorrect.', 401);
                }
            } else {
                return $this->sendError('The provided email is not registred.', 400);
            }
        } catch (Exception $e) {
            return $e->getMessage();
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function logout(Request $request)
    {
        try {

            $request->user()->tokens()->delete();

            User::where('id', Auth::User()->id)
                ->update(['api_token' => NULL]);

            return $this->sendSuccess('', 'Logout Succesfull.');
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
