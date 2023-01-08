<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *      @OA\Xml(name="GoogleColors"),
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          readOnly="true",
 *          example="1"
 *      ),
 *      @OA\Property(
 *          property="google_color_id",
 *          type="integer",
 *          readOnly="true",
 *          example="1",
 *          description="Color identifier used by google"
 *      ),
 *      @OA\Property(
 *          property="background",
 *          type="string",
 *          readOnly="true",
 *          description="Color Haxadecimal"
 *      ),
 *      @OA\Property(
 *          property="foreground",
 *          type="string",
 *          readOnly="true",
 *          description="Color Haxadecimal"
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          readOnly="true",
 *          description="Description of the color entered by the user"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          type="string",
 *          readOnly="true",
 *          description="Status that the color represents"
 *      ),
 *      @OA\Property(
 *          property="created_by",
 *          ref="#/components/schemas/User",
 *      ),
 *      @OA\Property(
 *          property="created_at", 
 *          ref="#/components/schemas/BaseModel/properties/created_at"
 *      ),
 *      @OA\Property(
 *          property="updated_at", 
 *          ref="#/components/schemas/BaseModel/properties/updated_at"
 *      ),
 *  )
 * 
 *  class GoogleColors
 */

class GoogleColors extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_color_id',
        'background',
        'foreground',
        'description',
        'status',
        'created_by',
    ];

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
