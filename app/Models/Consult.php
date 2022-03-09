<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $location
 * @property string $timezone
 * @property Carbon $startTime
 * @property Carbon $endTime
 * @property array $today
 * @property array $next_days
 * @property Carbon $created_at
 */
class Consult extends Model
{
    protected $fillable = [
        'location',
        'timezone',
        'startTime',
        'endTime',
        'today',
        'next_days',
        'created_at',
    ];

    protected $casts = [
        'today'      => 'array',
        'next_days'  => 'array',
        'startTime'  => 'datetime',
        'endTime'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected function startTime(): Attribute
    {
        return Attribute::set(
            fn ($value) => $value->setMinute(0)->setSecond(0)->setMicrosecond(0)
        );
    }
}
