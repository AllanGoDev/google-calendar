<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
