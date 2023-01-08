<?php

namespace App\Http\Controllers;

use App\Models\GoogleCredentials;
use App\Models\Log;
use App\Services\GoogleClient;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class GoogleEventsController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
    }

    /**
     * @OA\Get(
     *  path="api/google/events/list",
     *  tags={"Eventos"},
     *  summary="Lista Eventos",
     *  description="A rota realiza a listagem de eventos associados ao usuário conectado",
     *  operationId="listEvents",
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *   description="Id da credencial",
     *   in="path",
     *   name="credentialId",
     *   required=false
     *  ),
     *  @OA\Parameter(
     *   description="Initial date filter",
     *   in="path",
     *   name="timeMin",
     *   required=false,
     *   example="2022-12-27 00:00:00"
     *  ),
     *  @OA\Parameter(
     *   description="Finally date filter",
     *   in="path",
     *   name="timeMax",
     *   required=false,
     *   example="2022-12-30 00:00:00"
     *  ),
     *  @OA\Parameter(
     *   description="Id Evento",
     *   in="path",
     *   name="iCalUID",
     *   required=false,
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="The start date must be greater than the end date"
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
     *        example="[Lista com eventos]"
     *     ),
     *    ),
     *   )
     * )
     */
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

        if (!empty($request->only('credentialId'))) {
            $credential = GoogleCredentials::where([
                'id' => $request->only('credentialId'),
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

    /**
     * @OA\Get(
     *  path="google/events/show",
     *  tags={"Eventos"},
     *  summary="Lista um evento",
     *  description="A rota realiza a exibição de um evento expecifico",
     *  operationId="showEvent",
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *   description="Id da credencial",
     *   in="path",
     *   name="credentialId",
     *   required=false
     *  ),
     *  @OA\Parameter(
     *   description="Id Evento",
     *   in="path",
     *   name="eventId",
     *   required=true,
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="No event id provided"
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
     *        example="[Objecto do evento]"
     *     ),
     *    ),
     *   )
     * )
     */
    public function showEvent(Request $request)
    {
        if (empty($request->eventId)) {
            return response()->json([
                'message' => 'No event id provided'
            ], 400);
        }

        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        if (!empty($request->only('credentialId'))) {
            $credential = GoogleCredentials::where([
                'id' => $request->only('credentialId'),
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

    /**
     * @OA\Delete(
     *  path="api/google/events/remove",
     *  tags={"Eventos"},
     *  summary="Deleta Credencial",
     *  description="A rota realiza a deleção da credencial associada ao usuário",
     *  operationId="removeEvent",
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *   description="Id da credencial",
     *   in="path",
     *   name="credentialId",
     *   required=false
     *  ),
     *  @OA\Parameter(
     *   description="Id Evento",
     *   in="path",
     *   name="eventId",
     *   required=true,
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="No event id provided"
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
     *     )
     *    ),
     *  ),
     *  @OA\Response(
     *   response=201,
     *   description="Success",
     *   @OA\JsonContent(
     *     @OA\Property(
     *        property="message", 
     *        type="string", 
     *        example="Event removed successfully"
     *     ),
     *    ),
     *   )
     * )
     */
    public function removeEvent(Request $request)
    {
        if (empty($request->eventId)) {
            return response()->json([
                'message' => 'No event id provided'
            ], 400);
        }

        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        if (!empty($request->only('credentialId'))) {
            $credential = GoogleCredentials::where([
                'id' => $request->only('credentialId'),
                'user_id' => auth()->user()->id
            ])->first();
        } else {
            $credential = GoogleCredentials::where([
                'user_id' => auth()->user()->id
            ])->first();
        }

        try {
            $service->events->delete(@$credential->google_calendar_id, $request->eventId);
            return response()->json([
                'message' => 'Event removed successfully'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'stack' => $e->getTrace()
            ], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * @OA\Post(
     *  path="api/google/events/create",
     *  tags={"Eventos"},
     *  summary="Cria um evento",
     *  description="A rota realiza a criação de um evento na agenda do usuário conectado",
     *  operationId="createEvent",
     *  security={ {"bearerAuth": {} }},
     *  @OA\RequestBody(
     *   required=true,
     *   description="Dados do evento",
     *   @OA\JsonContent(),
     *   @OA\MediaType(
     *    mediaType="application/xml",
     *    @OA\Schema(type="object", ref="#/components/schemas/BaseEvent"),
     *   ),
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="Ops, check the data and try again"
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
     *        example="Successfully created event"
     *     ),
     *    ),
     *   )
     * )
     */
    public function createEvent(Request $request)
    {
        $eventData = $request->all();

        if (empty($eventData)) {
            return response()->json([
                'message' => 'Please enter the creation data'
            ], 400);
        }

        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        $event = new \Google\Service\Calendar\Event($eventData);

        if (!empty($request->only('credentialId'))) {
            $credential = GoogleCredentials::where([
                'id' => $request->only('credentialId'),
                'user_id' => auth()->user()->id
            ])->first();
        } else {
            $credential = GoogleCredentials::where([
                'user_id' => auth()->user()->id
            ])->first();
        }

        $result = $service->events->insert(@$credential->google_calendar_id, $event);

        if (empty(@$result->htmlLink)) {
            return response()->json([
                'message' => 'Ops, check the data and try again',
            ], 400, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'message' => 'Successfully created event',
            'event' => $result
        ]);
    }

    /**
     * @OA\Put(
     *  path="api/google/events/update",
     *  tags={"Eventos"},
     *  summary="Atualiza um evento",
     *  description="A rota realiza a criação de credenciais de configurações da integração",
     *  operationId="updateEvent",
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *   description="Id da credencial",
     *   in="path",
     *   name="credentialId",
     *   required=false
     *  ),
     *  @OA\Parameter(
     *   description="Id do evento",
     *   in="path",
     *   name="eventId",
     *   required=false
     *  ),
     * @OA\RequestBody(
     *   required=true,
     *   description="User credentials config google",
     *   description="Dados do evento",
     *   @OA\JsonContent(),
     *    @OA\MediaType(
     *     mediaType="application/xml",
     *     @OA\Schema(type="object", ref="#/components/schemas/BaseEvent"),
     *   ),
     *  ),
     *  @OA\Response(
     *   response=400,
     *   description="Error",
     *   @OA\JsonContent(
     *     @OA\Property(
     *       property="message", 
     *       type="string", 
     *       example="No event id provided"
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
     *        example="successfully updated event"
     *     ),
     *    ),
     *   )
     * )
     */
    public function updateEvent(Request $request)
    {
        if (empty($request->eventId)) {
            return response()->json([
                'message' => 'No event id provided'
            ], 400);
        }

        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        if (!empty($request->only('credentialId'))) {
            $credential = GoogleCredentials::where([
                'id' => $request->only('credentialId'),
                'user_id' => auth()->user()->id
            ])->first();
        } else {
            $credential = GoogleCredentials::where([
                'user_id' => auth()->user()->id
            ])->first();
        }

        $eventData = $request->all();

        if (!empty($eventData['eventId'])) unset($eventData['eventId']);

        if (!empty($eventData['credentialId'])) unset($eventData['credentialId']);

        if (empty($eventData)) {
            return response()->json([
                'message' => 'Please enter the update fields'
            ], 400);
        }

        $event = $service->events->get(@$credential->google_calendar_id, $request->eventId);

        foreach ($eventData as $key => $value) {
            $set = 'set' . ucfirst($key);
            $dado = $value;
            switch ($key) {
                case 'start':
                    $dado = new \Google\Service\Calendar\EventDateTime($value);
                    break;
                case 'end':
                    $dado = new \Google\Service\Calendar\EventDateTime($value);
                    break;
                case 'reminders':
                    $dado = new \Google\Service\Calendar\EventReminders($value);
                    break;
                case 'extendedProperties':
                    $dado = new \Google\Service\Calendar\EventExtendedProperties($value);
                    break;
                case 'gadget':
                    $dado = new \Google\Service\Calendar\EventGadget($value);
                    break;
                case 'originalStartTime':
                    $dado = new \Google\Service\Calendar\EventDateTime($value);
                    break;
                case 'source':
                    $dado = new \Google\Service\Calendar\EventSource($value);
                    break;
            }

            $event->$set($dado);
        }

        $eventUpdated = $service->events->update(@$credential->google_calendar_id, $event->getId(), $event);

        if (empty(@$eventUpdated)) {
            return response()->json([
                'message' => 'Ops, check the data and try again',
            ], 400, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'message' => 'successfully updated event',
            'event' => $eventUpdated
        ]);
    }

    public function watchEvent(Request $request): JsonResponse
    {
        if (empty($request->eventId)) {
            return response()->json([
                'message' => 'No event id provided'
            ], 400);
        }

        $client = $this->googleClient->getUserClient();

        $service = new \Google\Service\Calendar($client);

        if (!empty($request->only('credentialId'))) {
            $credential = GoogleCredentials::where([
                'id' => $request->only('credentialId'),
                'user_id' => auth()->user()->id
            ])->first();
        } else {
            $credential = GoogleCredentials::where([
                'user_id' => auth()->user()->id
            ])->first();
        }

        $body = new \Google\Service\Calendar\Channel([
            'id' => $request->eventId,
            'type' => 'webhook',
            'address' => !(empty($request->googleWebhookUri)) ? $request->googleWebhookUri : @$credential->google_webhook_uri
        ]);

        $result = $service->events->watch(@$credential->google_calendar_id, $body);

        if (!$result) {
            return response()->json([
                'message' => 'Ops, an unexpected error occurred, please try again'
            ], 500);
        }

        return response()->json([
            'message' => 'Event watch created successfully',
            'result' => $result
        ], 200);
    }

    public function webhookEvent(Request $request)
    {
        $url = $request->url();
        $method = $request->method();

        Log::create([
            'uuid' => Uuid::uuid4(),
            'method' => $method,
            'route' => $url,
            'response_data' => json_encode($request)
        ]);

        // $archive = fopen('response.txt', 'w');
        // fwrite($archive, join("\n", [
        //     'url: '  . $url,
        //     'method: ' . $method,
        //     $request
        // ]));
        // fclose($archive);
        return response()->json([
            'message' => 'Webhook event created successfully',
        ], 200);
    }
}
