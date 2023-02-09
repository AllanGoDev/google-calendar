<?php

namespace App\Http\Controllers;

use App\Models\GoogleCredentials;
use App\Models\User;
use Google\Service\ArtifactRegistry\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleCredentialsController extends Controller
{
    /**
     * @OA\Post(
     *  path="api/google/credentials/register-key",
     *  tags={"Credentials Google"},
     *  summary="Cria credenciais de configuração",
     *  description="A rota realiza a criação de credenciais de configurações da integração",
     *  operationId="createCredential",
     *  security={ {"bearerAuth": {} }},
     *  @OA\RequestBody(
     *   required=true,
     *   description="User credentials config google",
     *   @OA\JsonContent(
     *      @OA\Property(
     *        property="google_calendar_id", 
     *        type="string", 
     *        example=""
     *      ),
     *      @OA\Property(
     *        property="google_client_id", 
     *        type="string", 
     *        example=""
     *      ),
     *      @OA\Property(
     *        property="google_client_secret", 
     *        type="string", 
     *        example=""
     *      ),
     *      @OA\Property(
     *        property="google_redirect_uri", 
     *        type="string", 
     *        example=""
     *      ),
     *      @OA\Property(
     *        property="google_webhook_uri", 
     *        type="string", 
     *        example=""
     *      ),
     *    )
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *      property="message", 
     *      type="string", 
     *      example="Invalid credentials"
     *     )
     *   ),
     *  ),
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     *   @OA\JsonContent(
     *    @OA\Property(
     *      property="user",
     *      ref="#/components/schemas/User"
     *    ),
     *    @OA\Property(
     *      property="credential",
     *      ref="#/components/schemas/GoogleCredentials"
     *    )
     *   ),
     *  ),
     * )
     */
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

    /**
     * @OA\Get(
     *  path="api/google/credentials/list-keys",
     *  tags={"Credentials Google"},
     *  summary="Lista Credenciais",
     *  description="A rota realiza a listagem de credenciais associados ao usuário conectado",
     *  operationId="listCredentials",
     *  security={ {"bearerAuth": {} }},
     *  @OA\Response(
     *   response=204,
     *   description="Success",
     *   @OA\JsonContent(
     *     @OA\Property(
     *      property="message", 
     *      type="string", 
     *      example="Ops, no credentials registered"
     *     )
     *   ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(ref="#/components/schemas/GoogleCredentials"),
     *   )
     * )
     */
    public function listCredentials(Request $request): JsonResponse
    {
        $where = [
            'user_id' => auth()->user()->id
        ];

        if (!empty($request->GoogleCalendarId)) {
            $where['google_calendar_id'] = $request->GoogleCalendarId;
        }

        $credentials = GoogleCredentials::with('user:id,name,email,provider_id')
            ->where($where)
            ->get()
            ->toArray();

        if (!$credentials || empty($credentials)) {
            return response()->json([
                'message' => 'Ops, no credentials registered'
            ], 401);
        }

        return response()->json(['credentials' => $credentials], 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * @OA\Put(
     *  path="api/google/credentials/update-key",
     *  tags={"Credentials Google"},
     *  summary="Atualiza Credencial",
     *  description="A rota realiza a atualização da credencial associada ao usuário",
     *  operationId="updateCredential",
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *   description="Id da credencial",
     *   in="path",
     *   name="credentialId",
     *   required=true
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="Please enter credentialId"
     *     )
     *    ),
     *  ),
     *  @OA\Response(
     *   response=500,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="Ops, an error occurred when performing the update"
     *     )
     *    ),
     *  ),
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     *   @OA\JsonContent(
     *     @OA\Property(
     *        property="message", 
     *        type="string", 
     *        example="Credential successfully updated"
     *     ),
     *     @OA\Property(
     *        property="color",
     *        ref="#/components/schemas/GoogleCredentials"
     *     )
     *    ),
     *   )
     * )
     */
    public function updateCredential(Request $request): JsonResponse
    {

        if (empty($request->credentialId)) {
            return response()->json([
                'message' => 'Please enter credentialId'
            ], 400);
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

    /**
     * @OA\Delete(
     *  path="google/credentials/delete-key",
     *  tags={"Credentials Google"},
     *  summary="Deleta Credencial",
     *  description="A rota realiza a deleção da credencial associada ao usuário",
     *  operationId="deleteCredential",
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *   description="Id da credencial",
     *   in="path",
     *   name="credentialId",
     *   required=true
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="Please enter credentialId"
     *     )
     *    ),
     *  ),
     *  @OA\Response(
     *   response=500,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="The credential reported does not exist"
     *     )
     *    ),
     *  ),
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     *   @OA\JsonContent(
     *     @OA\Property(
     *        property="message", 
     *        type="string", 
     *        example="Credential successfully deleted"
     *     ),
     *    ),
     *   )
     * )
     */
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
