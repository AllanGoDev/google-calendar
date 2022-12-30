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

    public function updateColors(Request $request)
    {
        if (empty($request->colorId)) {
            return response()->json([
                'message' => 'Please enter credentialId'
            ]);
        }

        if (!$color = GoogleColors::where(['id' => $request->colorId, 'created_by' => auth()->user()->id])->first()) {
            return response()->json([
                'message' => 'Ops, an error occurred when performing the update'
            ]);
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
