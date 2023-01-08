<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *  @OA\Xml(name="BaseEvent"),
 *  @OA\Property(
 *   property="summary",
 *   type="string",
 *   readOnly="false",
 *   example="Titulo do Evento"
 *  ),
 *  @OA\Property(
 *   property="location",
 *   type="string",
 *   readOnly="true",
 *   example="São Paulo, SP, Brasil"
 *  ),
 *  @OA\Property(
 *   property="description",
 *   type="string",
 *   readOnly="true",
 *   example="Aqui podemos inserir uma boa descrição"
 *  ),
 *  @OA\Property(
 *   property="status",
 *   type="string",
 *   readOnly="true",
 *   example="confirmed"
 *  ),
 *  @OA\Property(
 *   property="start",
 *   type="array",
 *   readOnly="true",
 *   @OA\Items(
 *    @OA\Property(
 *      property="dateTime",
 *      type="string",
 *      format="date-time",
 *      example="2022-12-31T18:00:00-07:00"
 *    ),
 *    @OA\Property(
 *      property="timeZone",
 *      type="string",
 *      example="America/Sao_Paulo"
 *     )
 *   )
 *  ),
 *  @OA\Property(
 *   property="end",
 *   type="array",
 *   readOnly="true",
 *   @OA\Items(
 *    @OA\Property(
 *      property="dateTime",
 *      type="string",
 *      format="date-time",
 *      example="2023-01-01T18:00:00-07:00"
 *    ),
 *    @OA\Property(
 *      property="timeZone",
 *      type="string",
 *      example="America/Sao_Paulo"
 *     )
 *   )
 *  ),
 *  @OA\Property(
 *    property="recurrence",
 *    type="array",
 *    readOnly="true",
 *    @OA\Items(
 *      type="string",
 *      example={"RRULE:FREQ=DAILY;COUNT=2"}
 *    )
 *  ),
 *  @OA\Property(
 *    property="attendees",
 *    type="array",
 *    readOnly="true",
 *    @OA\Items(
 *     @OA\Property(
 *      property="email",
 *      type="string",
 *      format="email",
 *      example="lpage@example.com"
 *    ),
 *   ),
 *  ),
 *  @OA\Property(
 *    property="reminders",
 *    type="array",
 *    readOnly="true",
 *    @OA\Items(
 *     @OA\Property(
 *      property="useDefault",
 *      type="boolean",
 *      readOnly="true",
 *      example="true"
 *     ),
 *     @OA\Property(
 *      property="overrides",
 *      type="array",
 *      readOnly="true",
 *      @OA\Items(
 *       @OA\Property(
 *        property="method",
 *        type="string",
 *        example="email"
 *       ), 
 *       @OA\Property(
 *        property="minutes",
 *        type="integer",
 *        example="1500"
 *       ), 
 *      )
 *     )
 *   )
 *  )
 * )
 */
abstract class BaseEvent extends Model
{
}
