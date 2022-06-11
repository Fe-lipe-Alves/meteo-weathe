<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageWeather extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'image_id',
        'weather_id',
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function weather()
    {
        return $this->belongsTo(Weather::class);
    }
}
