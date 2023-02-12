<?php

namespace App\Services;

use App\Models\User;

class GoogleClient
{
  public function getClient(): \Google_Client
  {
    $configJson = base_path() . '/config.json';

    $applicationName = 'IntegrationGoogleCalendar';

    $client = new \Google_Client();
    $client->setApplicationName($applicationName);
    $client->setScopes([
      \Google\Service\Oauth2::USERINFO_PROFILE,
      \Google\Service\Oauth2::USERINFO_EMAIL,
      \Google\Service\Oauth2::OPENID,
      \Google\Service\Calendar::CALENDAR,
    ]);
    $client->setIncludeGrantedScopes(true);
    $client->setAuthConfig($configJson);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    return $client;
  }

  public function getUserClient(): \Google_Client
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
}
