<?php

namespace App\Http\Controllers;

use App\Models\GoogleCredentials;
use App\Models\User;
use Google\Service\ArtifactRegistry\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleCredentialsController extends Controller
{
    public function createCredential(Request $request): JsonResponse
    {
        $user = User::where('id', auth()->user()->id)->first();

        if (!$user) {
            return response([
                'message' => 'Invalid credentials'
            ], 400);
        }

        $googleCredential = GoogleCredentials::create([
            'google_calendar_id' => @$request->google_calendar_id ?: null,
            'google_client_id' => @$request->google_client_id ?: null,
            'google_client_secret' => @$request->google_client_secret ?: null,
            'google_redirect_uri' => @$request->google_redirect_uri ?: null,
            'google_webhook_uri' => @$request->google_webhook_uri ?: null,
            'user_id' => $user->id
        ]);

        return response()->json([
            'user' => $user,
            'credential' => $googleCredential
        ], 200);
    }

    public function listCredentials(Request $request): JsonResponse
    {
        $credentials = GoogleCredentials::with('user')
            ->where([
                'user_id' => auth()->user()->id
            ])
            ->get()
            ->toArray();

        if (!$credentials) {
            return response([
                'message' => 'Ops, no credentials registered'
            ], 204);
        }

        return response()->json(['credentials' => $credentials], 200);
    }

    public function updateCredential(Request $request, $id): JsonResponse
    {
        if (!$credential = GoogleCredentials::where(['id' => $id])->first()) {
            return response()->json([
                'message' => 'Ops, an error occurred when performing the update'
            ], 500);
        }

        $credential->update($request->all());

        return response()->json([
            'message' => 'Credential successfully updated'
        ], 201);
    }

    public function deleteCredential($id): JsonResponse
    {
        if (!$credential = GoogleCredentials::where(['id' => $id])->first()) {
            return response()->json([
                'message' => 'The credential reported does not exist'
            ], 500);
        }

        $credential->delete();

        return response()->json([
            'message' => 'Credential successfully deleted'
        ], 201);
    }
}
