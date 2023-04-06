<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleMonitoringEvents extends Model
{
  use HasFactory;

  protected $fillable = [
    'event_id',
    'resource_id',
    'monitoring',
    'user_id'
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }
}
