<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    @OA\Xml(name="GoogleCredentials"),
 *    @OA\Property(
 *      property="id",
 *      type="integer",
 *      readOnly="true",
 *      example="1"
 *    ),
 *    @OA\Property(
 *      property="google_calendar_id",
 *      type="text",
 *      readOnly="true"
 *    ),
 *    @OA\Property(
 *      property="google_client_id",
 *      type="text",
 *      readOnly="true"
 *    ),
 *    @OA\Property(
 *      property="google_client_secret",
 *      type="text",
 *      readOnly="true"
 *    ),
 *    @OA\Property(
 *      property="google_redirect_uri",
 *      type="text",
 *      readOnly="true",
 *      description="Redirection route evoked by google on Oauth2 login"
 *    ),
 *    @OA\Property(
 *      property="google_webhook_uri",
 *      type="text",
 *      readOnly="true",
 *      description="Redirection route evoked by when there is a change in the event"
 *    ),
 *    @OA\Property(
 *      property="user",
 *      ref="#/components/schemas/User",
 *    ),
 *    @OA\Property(
 *       property="created_at", 
 *       ref="#/components/schemas/BaseModel/properties/created_at"
 *    ),
 *    @OA\Property(
 *       property="updated_at", 
 *       ref="#/components/schemas/BaseModel/properties/updated_at"
 *    ),
 * )
 * 
 * Class GoogleCredentials
 */

class GoogleCredentials extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_calendar_id',
        'google_client_id',
        'google_client_secret',
        'google_redirect_uri',
        'google_webhook_uri',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
