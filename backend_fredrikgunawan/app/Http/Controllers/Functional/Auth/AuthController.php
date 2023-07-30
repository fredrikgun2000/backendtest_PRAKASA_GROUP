<?php

namespace App\Http\Controllers\Functional\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class AuthController extends Controller
{
    public function Login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {    
            $message = $validator->errors()->getMessages();
            $api = array(
                'message' => $message
            );
            return response()->json($api,400);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->accessToken;
            $data = array(
                'token' => $token
            );

            $message = 'Successfully logged in';
            return ApiResponse::success($data, $message);
        } else {
            $message = 'Invalid credentials';
            return ApiResponse::error($message, 400);
        }
    }

    public function Logout(Request $request) {
        Auth::user()->token()->revoke();
        $data = array();
        $message = 'Successfully logged out';
        return ApiResponse::success($data, $message);
    }

    public function Refresh(Request $request) {
        Auth::user()->token()->revoke();
        $token = $request->user()->createToken('authToken')->accessToken;
        $data = array(
            'token' => $token
        );
        $message = 'Successfully refreshed token';
        return ApiResponse::success($data, $message);
    }

    public function Me(Request $request) {
        $user = Auth::user();
        $message = 'Successfully retrieved user';
        return ApiResponse::success($user, $message);
    }
}

