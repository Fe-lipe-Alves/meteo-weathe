<?php

namespace App\Models;

use App\Support\Enums\Period;
use App\Support\Enums\Weather;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'path',
        'period',
        'weather',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'period' => Period::class,
        'weather' => Weather::class,
    ];

    /**
     * Obtém a imagem conforme o código recebido
     *
     * @param int|string $weatherCode
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getByWeather(int|string $weatherCode)
    {
        return self::query()
            ->where([
                'weather' => Weather::get($weatherCode),
                'active' => true,
            ])
            ->inRandomOrder()
            ->first();
    }
}
