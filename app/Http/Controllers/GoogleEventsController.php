<?php

namespace App\Http\Controllers;

use App\Models\GoogleCredentials;
use App\Services\GoogleClient;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleEventsController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
    }

    public function listEvents(Request $request): JsonResponse
    {
        $filters = [];

        if (!empty($request->timeMin)) {
            $timeMin = new DateTime($request->timeMin);
            $filters['timeMin'] = $timeMin->format(DATE_RFC3339);
        }

        if (!empty($request->timeMax)) {
            $timeMax = new DateTime($request->timeMax);
            $filters['timeMax'] = $timeMax->format(DATE_RFC3339);
        }

        if (!empty($request->iCalUID)) {
            $filters['iCalUID'] = $request->iCalUID;
        }

        if (!empty($filters['timeMax']) && !empty($filters['timeMin']) && $filters['timeMin'] >= $filters['timeMax']) {
            return response()->json([
                'message' => 'The start date must be greater than the end date'
            ], 400);
        }

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

        $result = $service->events->listEvents(@$credential->google_calendar_id, $filters);

        return response()->json($result->getItems(), 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function showEvent(Request $request)
    {
        if (empty($request->eventId)) {
            return response()->json([
                'message' => 'No event id provided'
            ], 400);
        }

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

        $result = $service->events->get(@$credential->google_calendar_id, $request->eventId);

        return response()->json($result, 200, [], JSON_UNESCAPED_SLASHES);
    }
}
