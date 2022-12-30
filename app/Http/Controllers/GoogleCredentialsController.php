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
            'google_calendar_id' => @$request->googleCalendarId ?: null,
            'google_client_id' => @$request->googleClientId ?: null,
            'google_client_secret' => @$request->googleRedirectUri ?: null,
            'google_redirect_uri' => @$request->googleWebhookUri ?: null,
            'google_webhook_uri' => @$request->googleClientSecret ?: null,
            'user_id' => $user->id
        ]);

        return response()->json([
            'user' => $user,
            'credential' => $googleCredential
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function listCredentials(Request $request): JsonResponse
    {
        $credentials = GoogleCredentials::with('user:id,name,email,provider_id')
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

        return response()->json(['credentials' => $credentials], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function updateCredential(Request $request): JsonResponse
    {

        if (empty($request->credentialId)) {
            return response()->json([
                'message' => 'Please enter credentialId'
            ]);
        }

        if (!$credential = GoogleCredentials::where(['id' => $request->credentialId])->first()) {
            return response()->json([
                'message' => 'Ops, an error occurred when performing the update'
            ], 500);
        }

        $aDados = $request->all();
        $aUpdated = [];

        if (!empty($aDados['googleCalendarId']))
            $aUpdated['google_calendar_id'] = $aDados['googleCalendarId'];

        if (!empty($aDados['googleClientId']))
            $aUpdated['google_client_id'] = $aDados['googleClientId'];

        if (!empty($aDados['googleRedirectUri']))
            $aUpdated['google_client_secret'] = $aDados['googleRedirectUri'];

        if (!empty($aDados['googleWebhookUri']))
            $aUpdated['google_redirect_uri'] = $aDados['googleWebhookUri'];

        if (!empty($aDados['googleClientSecret']))
            $aUpdated['google_webhook_uri'] = $aDados['googleClientSecret'];

        $credentialUpdated = $credential->updateOrCreate(
            [
                'id' => $request->credentialId
            ],
            $aUpdated
        );

        return response()->json([
            'message' => 'Credential successfully updated',
            'credential' => $credentialUpdated
        ], 201, [], JSON_UNESCAPED_SLASHES);
    }

    public function deleteCredential(Request $request): JsonResponse
    {
        if (empty($request->credentialId)) {
            return response()->json([
                'message' => 'Please enter credentialId'
            ]);
        }

        if (!$credential = GoogleCredentials::where(['id' => $request->credentialId])->first()) {
            return response()->json([
                'message' => 'The credential reported does not exist'
            ], 500);
        }

        $credential->delete();

        return response()->json([
            'message' => 'Credential successfully deleted'
        ], 201, [], JSON_UNESCAPED_SLASHES);
    }
}
