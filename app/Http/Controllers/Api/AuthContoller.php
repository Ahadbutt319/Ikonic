<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class AuthContoller extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:App\Models\User,email',
                'password' => 'required|confirmed|min:6',
                'name' => 'required',
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first()
                ], 422);
            }
                $user = new User();
                $user->name = $request->name;
                $user->password = Hash::make($request->password);
                $user->email = $request->email;
                $user->email_verified_at = now();
                $user->save();
                event(new Registered($user));
                $credentials = $request->only('email', 'password');
                if (Auth::attempt($credentials)) {
                    if ($request->is('api/*')) {
                        $device_name = ($request->device_name) ? $request->device_name : config("app.name");
                        return response()->json([
                            'code' => 200,
                            'message' => 'User logged in successfully',
                            'data' => [
                                'user' => Auth::user(),
                                'access_token' => Auth::user()->createToken($device_name)->plainTextToken,
                                'token_type' => 'Bearer',
                                // 'notification' =>  $notification,
                            ],
                        ]);
                    } else {
                        $request->session()->regenerate();
                        if ($request->expectsJson()) {
                            //$device_name = ($request->device_name) ? $request->device_name : config("app.name");
                            //$accessToken = Auth::user()->createToken($device_name)->plainTextToken;
                            $data = Auth::get();

                            return response()->json($data);
                        }
                        return redirect()->intended('/');
                    }
        
                return response()->json(["email" => $request->email, "password" => $request->password], 422);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors()->first()
            ], 422);
        }
        $remember_me = ($request->remember_me) ? true : false;
        $credentials = $request->only('email', 'password');
        // dd( Auth::attempt($credentials));
        if (Auth::attempt($credentials, $remember_me)) {
            if ($request->is('api/*')) {
                $device_name = ($request->device_name) ? $request->device_name : config("app.name");
                return response()->json([
                    'code' => 200,
                    'message' => 'User logged in successfully',
                    'data' => [
                        'user' => Auth::user(),
                        'access_token' => Auth::user()->createToken($device_name)->plainTextToken,
                        'token_type' => 'Bearer',
                        // 'notification' =>  $notification,
                    ],
                ]);
            }
        } else {
            return response()->json(['code' => 401, 'error' => 'The credentials are incorrect'], 401);
        }
    }
}

