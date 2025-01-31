<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    function __construct()

    {
        $this->middleware('permission:view-users', ['only' => ['getAllUsers']]);
    }
    public function register(Request $request)
    {
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $role = Role::where('name', 'user')->first();
        if ($role) {
            $user->assignRole($role);
        }

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user
        ], 200);
    }

    public function authenticate(Request $request)
    {

        $credentials = $request->only('email', 'password');


        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Login credentials are invalid.',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create token.' . $e,
            ], 500);
        }


        $user = JWTAuth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'status' => 'success',
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sorry, user cannot be logged out'
            ], 500);
        }
    }


    public function refreshToken()
    {
        try {

            $newToken = JWTAuth::refresh();
            return response()->json([
                'status' => 'success',
                'user' => JWTAuth::user(),
                'authorization' => [
                    'token' => $newToken,
                    'type' => 'bearer',
                ]
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh token.',
            ], 500);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired.',
            ], 401);
        }
    }

    public function getAllUsers()
    {
        $users = User::all();
        return response()->json([
            'status' => 'success',
            'users' => $users
        ], 200);
    }
}
