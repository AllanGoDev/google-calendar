<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
    public function savedKeyGoogle(Request $request)
    {
        if (!$user = auth()->user()) {
            return response([
                'message' => 'Logged is required'
            ]);
        }

        $request->validate([
            'key-google' => 'required|string'
        ]);

        try {
            User::where('id', $user->id)
                ->update([
                    'key_google' => $request->get('key-google')
                ]);
            return response([
                'message' => 'Key Google saved successfully',
            ], 201);
        } catch (Throwable $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
