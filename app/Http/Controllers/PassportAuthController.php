<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PassportAuthController extends Controller
{
    /** Registration */
    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($user = User::where(['email' => $request->email])->first()) {
            return response()->json(['message' => 'User already exists']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('LaravelAuthApp')->accessToken;

        return response()->json(['user' => $user, 'token' => $token], 200);
    }


    /**
     * Login
     */
    public function login(Request $request): JsonResponse
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['user' => auth()->user(), 'token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**Logout */
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = [
            'message' => 'You have been successfully logged out'
        ];
        return response($response, 200);
    }
}
