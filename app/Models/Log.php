<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'method',
        'route',
        'request_status',
        'request_data',
        'response_status',
        'response_data',
    ];
}
