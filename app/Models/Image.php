<?php

namespace App\Models;

use App\Support\Enums\Period;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    protected $fillable = [
        'path',
        'period',
    ];

    protected $casts = [
        'period' => Period::class,
    ];

    public function weathers()
    {
        return $this->belongsToMany(Weather::class);
    }

    public function getPathAttribute()
    {
        return Storage::url($this->attributes['path']);
    }
}
