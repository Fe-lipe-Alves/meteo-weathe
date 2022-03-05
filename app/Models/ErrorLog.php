<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'http_status',
        'request',
        'response',
        'headers',
        'created_at',
    ];

    protected $casts = [
        'response'   => 'array',
        'headers'    => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;
}
