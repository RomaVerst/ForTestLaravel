<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
//        $user->roles()->attach(2);
        return response()->json($user);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = $request->only('email', 'password');
        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => [
                    'password' => ['Invalid credentials']
                ]
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'access_token' => $token
        ]);
    }
}
