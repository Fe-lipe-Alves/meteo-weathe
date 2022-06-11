<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weather extends Model
{
    protected $table = 'weather';
    public $timestamps = false;

    protected $casts = [
        'weather' => \App\Support\Enums\Weather::class,
    ];

    public function images()
    {
        return $this->belongsToMany(Image::class);
    }

    /**
     * Obtém a imagem conforme o código recebido
     *
     * @param int|string $weatherCode
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public function getImageRamdon()
    {
        return $this->images()
            ->inRandomOrder()
            ->first();
    }
}
