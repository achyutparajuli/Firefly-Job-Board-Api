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
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails())
        {
            return $this->sendError($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password))
        {
            return $this->sendError('Invalid credentials.', 401);
        }

        if (!$user->email_verified_at)
        {
            return $this->sendError('Email not verified. Please verify your email.', 422);
        }

        if (!$user->status)
        {
            return $this->sendError('Account is inactive. Please contact the system admin.', 422);
        }

        try
        {
            $token = $user->createToken($user->email)->accessToken;

            $user->update(['api_token' => $token]);

            $responseData = [
                'name' => $user->name,
                'id' => $user->id,
                'role' => $user->role,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'profile_image' => $user->profile_image,
                'token' => $token,
            ];

            return $this->sendSuccess($responseData, 'Login successful.');
        }
        catch (Exception $e)
        {
            return $this->sendError('Error: ' . $e->getMessage(), 500);
        }
    }



    public function logout(Request $request)
    {
        try
        {
            $request->user()->tokens()->delete();

            User::where('id', Auth::User()->id)
                ->update(['api_token' => NULL]);

            return $this->sendSuccess('', 'Logout successful.');
        }
        catch (Exception $e)
        {
            return $this->sendError('Error: ' . $e->getMessage());
        }
    }
}
