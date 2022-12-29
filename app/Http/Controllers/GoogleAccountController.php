<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount as GoogleAccount;
use App\Models\GoogleCredentials;
use App\Models\User;
use App\Services\Google;
use App\Services\GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleAccountController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
    }

    public function getAuthUrl(Request $request): JsonResponse
    {

        $client = $this->googleClient->getClient();

        $authUrl = $client->createAuthUrl();

        return response()->json([
            'url' => $authUrl
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function getAuth(Request $request): JsonResponse
    {
        $authCode = urldecode($request->input('code'));

        $client = $this->googleClient->getClient();

        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        $client->setAccessToken(json_encode($accessToken));

        $service = new \Google\Service\Oauth2(($client));

        $userFromGoogle = $service->userinfo->get();

        $user = User::where('email', '=', $userFromGoogle->email)
            ->where('provider_id', '=', $userFromGoogle->id)
            ->first();

        if (!$user) {
            $user = User::create([
                'provider_id' => $userFromGoogle->id,
                'provider_name' => 'google',
                'google_access_token_json' => json_encode($accessToken),
                'name' => $userFromGoogle->name,
                'email' => $userFromGoogle->email
            ]);
        } else {
            $user->google_access_token_json = json_encode($accessToken);
            $user->save();
        }

        $token = $user->createToken("Google")->accessToken;
        return response()->json([
            'token' => $token
        ], 201);
    }



    public function getDrive(Request $request): JsonResponse
    {
        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        $parameters = [
            'calendarId' => "61d4f6dc3968c55272d4b8d05fffd836908c02249ff3e33e75af82864a651b76@group.calendar.google.com"
        ];

        $result = $service->events->listEvents($parameters['calendarId']);

        return response()->json($result->getItems(), 200);
    }

    public function listEvents(Request $request): JsonResponse
    {
        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        if (!empty($request->only('credential_id'))) {
            $credential = GoogleCredentials::where([
                'id' => $request->only('credential_id'),
                'user_id' => auth()->user()->id
            ])->first();
        } else {
            $credential = GoogleCredentials::where([
                'user_id' => auth()->user()->id
            ])->first();
        }

        $colors = $service->colors->get();
        dd($colors->getEvent());

        $result = $service->events->listEvents(@$credential->google_calendar_id);

        return response()->json($result->getItems(), 200, [], JSON_UNESCAPED_SLASHES);
    }
}
