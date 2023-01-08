<?php

namespace App\Http\Controllers;

use App\Models\GoogleColors;
use App\Models\GoogleCredentials;
use App\Services\GoogleClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleColorsController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
    }

    /**
     * @OA\Get(
     *     path="/api/google/colors/import",
     *     tags={"Cores"},
     *     summary="Importar cores",
     *     description="A rota realiza a importação das cores para a base de dados",
     *     operationId="importColors",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *               property="message", 
     *               type="string", 
     *               example="No colors to be imported"
     *             ),
     *             @OA\Property(
     *               property="total", 
     *               type="integer", 
     *               example="0"
     *             ),
     *             @OA\Property(
     *               property="success", 
     *               type="array", 
     *               collectionFormat="multi",
     *               @OA\Items(
     *                 type="string",
     *                 example={ "", ""},
     *               )
     *              ),
     *              @OA\Property(
     *                property="errors", 
     *                type="array", 
     *                collectionFormat="multi",
     *                @OA\Items(
     *                  type="string",
     *                  example={"", ""},
     *                )
     *              ),
     *       ),
     *    ),
     *    @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *               property="message", 
     *               type="string", 
     *               example="Successfully imported colors"
     *             ),
     *             @OA\Property(
     *               property="total", 
     *               type="integer", 
     *               example="0"
     *             ),
     *             @OA\Property(
     *               property="success", 
     *               type="array", 
     *                @OA\Items(
     *                  type="string",
     *                  example={
     *                      {"background", "foreground"}, 
     *                      {"background", "foreground"}, 
     *                  },
     *                )
     *              ),
     *              @OA\Property(
     *                property="errors", 
     *                type="array", 
     *                collectionFormat="multi",
     *                @OA\Items(
     *                  type="string",
     *                  example={
     *                      {"background", "foreground"}, 
     *                      {"background", "foreground"}, 
     *                  },
     *                )
     *              ),
     *       ),
     *    ),
     * )
     */
    public function importColors(Request $request): JsonResponse
    {
        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        $colors = $service->colors->get();

        $total = 0;
        $success = [];
        $errors = [];

        if (empty($colors->getEvent()))
            return response()->json([
                'message' => 'No colors to be imported',
                'total' => $total,
                'success' => $success,
                'errors' => $errors
            ], 201);

        foreach ($colors->getEvent() as $key => $value) {
            try {
                GoogleColors::updateOrCreate(
                    [
                        'google_color_id' => $key,
                        'created_by' => auth()->user()->id
                    ],
                    [
                        'google_color_id' => $key,
                        'background' => $value->background,
                        'foreground' => $value->foreground,
                        'created_by' => auth()->user()->id
                    ]
                );
                $success[$key] = $value;
            } catch (Exception $e) {
                $errors[$key] = $value;
            } finally {
                $total++;
            }
        }

        return response()->json([
            'message' => ($total == 0 || $success > 0) ? 'Successfully imported colors' : 'Ops, An error occurred while importing colors',
            'total' => $total,
            'success' => $success,
            'errors' => $errors
        ], ($total == 0 || $success > 0) ? 201 : 500);
    }

    /**
     * @OA\Get(
     *   path="api/google/colors/list",
     *   tags={"Cores"},
     *   summary="Lista as cores ",
     *   description="A rota realiza a listagem das rotas importadas pelo usuário logado",
     *   operationId="listColors",
     *   security={ {"bearerAuth": {} }},
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(ref="#/components/schemas/GoogleColors"),
     *   )
     * )
     */
    public function listColors(Request $request): JsonResponse
    {

        $colors = GoogleColors::with('created_by:id,name,email,provider_id')
            ->where([
                'created_by' => auth()->user()->id
            ])
            ->get()
            ->toArray();

        if (!$colors) {
            return response([
                'message' => 'Ops, no colors registered'
            ], 204);
        }

        return response()->json([
            'colors' => $colors
        ], 200);
    }

    /**
     * @OA\Put(
     *   path="api/google/colors/update",
     *   tags={"Cores"},
     *   summary="Atualiza uma cor na base",
     *   description="A rota realiza a atualização de uma cor já salva no banco",
     *   operationId="updateColors",
     *   security={ {"bearerAuth": {} }},
     *   @OA\Parameter(
     *     description="Id da cor na base",
     *     in="path",
     *     name="colorId",
     *     required=true
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Error",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message", 
     *         type="string", 
     *         example="Please enter credentialId"
     *       )
     *     ),
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Error",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message", 
     *         type="string", 
     *         example="Ops, an error occurred when performing the update"
     *       )
     *     ),
     *   ),
     *  @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *      @OA\Property(
     *         property="message", 
     *         type="string", 
     *         example="Color successfully updated"
     *       ),
     *      @OA\Property(
     *         property="color",
     *         ref="#/components/schemas/GoogleColors"
     *      )
     *    ),
     *   )
     * )
     */
    public function updateColors(Request $request)
    {
        if (empty($request->colorId)) {
            return response()->json([
                'message' => 'Please enter credentialId'
            ], 400);
        }

        if (!$color = GoogleColors::where(['id' => $request->colorId, 'created_by' => auth()->user()->id])->first()) {
            return response()->json([
                'message' => 'Ops, an error occurred when performing the update'
            ], 500);
        }

        $colorUpdated = $color->updateOrCreate(
            [
                'id' => $request->colorId,
                'created_by' => auth()->user()->id
            ],
            array_merge(
                $request->all(),
                ['created_by' => auth()->user()->id]
            )
        );

        return response()->json([
            'message' => 'Color successfully updated',
            'color' => $colorUpdated
        ], 201);
    }
}
