<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * @OA\Schema(
 *    required={"email", "password"},
 *    @OA\Xml(name="User"),
 *    @OA\Property(
 *        property="id", 
 *        type="integer", 
 *        readOnly="true", 
 *        example="1"
 *     ),
 *    @OA\Property(
 *       property="name", 
 *       type="string", 
 *       readOnly="true", 
 *       example="John Due"
 *    ),
 *    @OA\Property(
 *       property="email", 
 *       type="string", 
 *       readOnly="true", 
 *       format="email", 
 *       description="User unique email address",
 *       example="user@gmail.com"
 *    ),
 *    @OA\Property(
 *        property="provider_id", 
 *        type="string", 
 *        readOnly="true", 
 *        example="1051527092716338246212"
 *     ),
 *    @OA\Property(
 *        property="provider_name", 
 *        type="string", 
 *        readOnly="true", 
 *        example="google"
 *     ),
 *    @OA\Property(
 *       property="google_access_token_json", 
 *       type="string", 
 *       readOnly="true"
 *    ),
 *    @OA\Property(
 *       property="created_at", 
 *       ref="#/components/schemas/BaseModel/properties/created_at"
 *    ),
 *    @OA\Property(
 *       property="updated_at", 
 *       ref="#/components/schemas/BaseModel/properties/updated_at"
 *    ),
 *)
 * 
 * Class User
 */

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider_id',
        'provider_name',
        'google_access_token_json',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function google_credentials()
    {
        return $this->hasMany('App\Models\GoogleCredentials');
    }

    public function google_colors()
    {
        return $this->hasMany('App\Models\GoogleColors');
    }
}
