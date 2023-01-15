<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount as GoogleAccount;
use App\Models\GoogleCredentials;
use App\Models\User;
use App\Services\Google;
use App\Services\GoogleClient;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoogleAccountController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
    }

    /**
     * @OA\Get(
     *     path="/api/google/login/url",
     *     tags={"Login Oauth"},
     *     summary="Pegar url de login do oauth",
     *     description="A rota realiza a geração de uma url utilizada para autenticar com oauth",
     *     operationId="getAuthUrl",
     *      @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *          @OA\Property(
     *              property="url", 
     *              type="string", 
     *              example="https://accounts.google.com/o/oauth2/v2/auth?"
     *          ) 
     *         ),
     *     )
     * )
     */

    public function getAuthUrl(Request $request): JsonResponse
    {

        $client = $this->googleClient->getClient();

        $authUrl = $client->createAuthUrl();

        return response()->json([
            'url' => $authUrl
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * @OA\Post(
     *     path="google/auth/login",
     *     tags={"Login Oauth"},
     *     summary="Salvar credenciais de acesso oauth",
     *     description="A rota salva as credenciais de acesso necessários para a comunicação com a google",
     *     operationId="getAuth",
     *     @OA\Parameter(
     *       description="Codigo obtido no login realizado a partir da obtenção da url de login na rota /api/google/login/url",
     *       in="path",
     *       name="code",
     *       required=true
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Error",
     *         @OA\JsonContent(
     *          @OA\Property(
     *              property="message", 
     *              type="string", 
     *              example="Please enter the code"
     *          )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/BaseTokenGoogle"),
     *     )
     * )
     */
    public function getAuth(Request $request): JsonResponse
    {
        if (empty($request->input('code'))) {
            return response()->json([
                'message' => 'Please enter the code'
            ], 400, [], JSON_UNESCAPED_SLASHES);
        }

        $authCode = urldecode($request->input('code'));

        $client = $this->googleClient->getClient();

        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        $client->setAccessToken(json_encode($accessToken));

        $service = new \Google\Service\Oauth2(($client));

        $userFromGoogle = $service->userinfo->get();

        $user = User::where('email', '=', $userFromGoogle->email)
            ->first();

        if (!$user) {
            $user = User::create([
                'provider_id' => @$userFromGoogle->id,
                'provider_name' => 'google',
                'google_access_token_json' => json_encode($accessToken),
                'name' => @$userFromGoogle->name,
                'email' => @$userFromGoogle->email,
                'password' => bcrypt(Str::random(10))
            ]);
        } else {
            $user->google_access_token_json = json_encode($accessToken);
            $user->save();
        }

        $token = $user->createToken("Google")->accessToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // public function getDrive(Request $request): JsonResponse
    // {
    //     $client = $this->googleClient->getUserClient();

    //     $service = new \Google\Service\Calendar($client);

    //     $parameters = [
    //         'calendarId' => "61d4f6dc3968c55272d4b8d05fffd836908c02249ff3e33e75af82864a651b76@group.calendar.google.com"
    //     ];

    //     $result = $service->events->listEvents($parameters['calendarId']);

    //     return response()->json($result->getItems(), 200);
    // }
}
