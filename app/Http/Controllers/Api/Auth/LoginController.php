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
                return $this->sendError($validator->errors());
            }

            $checkUser = User::where('email', $request->email)->first();
            if ($checkUser) {
                if (!$checkUser->email_verified_at) {
                    return $this->sendError('Invalid Login. Please verify your email.');
                } else if (!$checkUser->status) {
                    return $this->sendError('Invalid Login. Please contact the system admin.');
                }

                if (Hash::check($request->password, $checkUser->password)) {
                    $token = $checkUser->createToken($checkUser->email)->accessToken;
                    $token = $token->token;

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
                    return $this->sendError('The provided password is incorrect.');
                }
            } else {
                return $this->sendError('The provided email is not registred.');
            }
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }

    public function logout(Request $request)
    {
        try {
            if (Auth::check()) {
                // Revoke the token
                if ($request->user()) {
                    $request->user()->token()->revoke();
                }
                User::where('id', Auth::User()->id)
                    ->update(['api_token' => NULL]);
            }

            return $this->sendSuccess('', 'Logout Succesfull.');
        } catch (Exception $e) {
            return $this->sendError('Error something went wrong! Please try again.');
        }
    }
}
