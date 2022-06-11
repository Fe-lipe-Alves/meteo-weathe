<?php

namespace Database\Seeders;

use App\Support\Enums\Weather;
use Illuminate\Database\Seeder;

class WeatherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Weather::cases() as $weather) {
            if (!\App\Models\Weather::query()->where('code', $weather->name)->exists()) {
                \App\Models\Weather::query()->create([
                    'code' => $weather->name,
                    'description' => $weather->value,
                ]);
            }
        }
    }
}
