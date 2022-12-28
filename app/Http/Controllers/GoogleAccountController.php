<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount as GoogleAccount;
use App\Models\User;
use App\Services\Google;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleAccountController extends Controller
{
    private function getClient(): \Google_Client
    {
        $configJson = base_path() . '/config.json';

        $applicationName = 'IntegrationGoogleCalendar';

        $client = new \Google_Client();
        $client->setApplicationName($applicationName);
        $client->setAuthConfig($configJson);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        $client->setScopes([
            \Google\Service\Oauth2::USERINFO_PROFILE,
            \Google\Service\Oauth2::USERINFO_EMAIL,
            \Google\Service\Oauth2::OPENID,
            \Google\Service\Calendar::CALENDAR,
        ]);

        $client->setIncludeGrantedScopes(true);
        return $client;
    }

    public function getAuthUrl(Request $request): JsonResponse
    {

        $client = $this->getClient();

        $authUrl = $client->createAuthUrl();

        return response()->json([
            'url' => $authUrl
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function getAuth(Request $request): JsonResponse
    {
        $authCode = urldecode($request->input('code'));

        $client = $this->getClient();

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

    private function getUserClient(): \Google_Client
    {
        $user = User::where('id', '=', auth()->user()->id)->first();

        $accessTokenJson = stripslashes($user->google_access_token_json);

        $client = $this->getClient();

        $client->setAccessToken($accessTokenJson);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $client->setAccessToken($client->getAccessToken());

            $user->google_access_token_json = json_encode($client->getAccessToken());
            $user->save();
        }

        return $client;
    }

    public function getDrive(Request $request): JsonResponse
    {
        $client = $this->getUserClient();

        $service = new \Google\Service\Calendar($client);

        $parameters = [
            'calendarId' => "61d4f6dc3968c55272d4b8d05fffd836908c02249ff3e33e75af82864a651b76@group.calendar.google.com"
        ];

        $result = $service->events->listEvents($parameters['calendarId']);

        return response()->json($result->getItems(), 200);
    }
}
