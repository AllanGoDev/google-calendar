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
            'client-id' => 'required|string',
            'api-key' => 'required|string'
        ]);

        try {
            User::where('id', $user->id)
                ->update([
                    'client_id' => $request->get('client-id'),
                    'api_key' => $request->get('api-key'),
                ]);
            return response([
                'message' => 'Keys Google saved successfully',
            ], 201);
        } catch (Throwable $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
