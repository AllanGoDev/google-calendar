<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    @OA\Xml(name="BaseTokenGoogle"),
 *    @OA\Property(
 *      property="id",
 *      type="integer",
 *      readOnly="true",
 *      example="1"
 *    ),
 *    @OA\Property(
 *      property="name",
 *      type="string",
 *      readOnly="true",
 *      example="Google"
 *    ),
 *    @OA\Property(
 *      property="abilities",
 *      type="array",
 *      readOnly="true",
 *      collectionFormat="multi",
 *      @OA\Items(
 *          type="string",
 *          example={
 *            "hability 1",
 *            "hability 2"
 *          },
 *       )
 *    ),
 *    @OA\Property(
 *      property="expires_at",
 *      type="string",
 *      readOnly="true",
 *      format="date-time"
 *    ),
 *    @OA\Property(
 *      property="tokenable_id",
 *      type="integer",
 *      readOnly="true",
 *      example="2"
 *    ),
 *    @OA\Property(
 *      property="tokenable_type",
 *      type="string",
 *      readOnly="true",
 *      example="App\\Models\\User"
 *    ),
 *    @OA\Property(
 *      property="created_at",
 *      type="string",
 *      readOnly="true",
 *      format="date-time"
 *    ),
 *    @OA\Property(
 *      property="updated_at",
 *      type="string",
 *      readOnly="true",
 *      format="date-time"
 *    ),
 * )
 * 
 * Class BaseTokenGoogle
 */

abstract class BaseTokenGoogle extends Model
{
}
