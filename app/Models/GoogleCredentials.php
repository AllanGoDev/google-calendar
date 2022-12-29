<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
