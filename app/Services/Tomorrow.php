<?php

namespace App\Services;

use App\Models\Consult;
use App\Models\ErrorLog;
use App\Support\Enums\FieldComparison;
use App\Support\Enums\Timestep;
use App\Support\Tomorrow\Field;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Tomorrow
{
    private string $baseUrl = 'https://api.tomorrow.io/v4/timelines';
    private string $apiKey = 'woUIhYHlbBiFaLX311Rv8NU0QmXC0MsP';
    private Carbon $startTime;
    private Carbon $endTime;
    private Consult|Model $result;

    /**
     * @throws Exception
     */
    public function __construct(
        private string $location,
        private string $timezone,
    )
    {
        $cache = $this->inCache();
        if (!$cache) {
            $this->consult();
        }
    }

    /**
     * Busca se a consulta já está salva no banco e a armazena na propriedade $result.
     * Retorna o booleano da sua existência no banco
     *
     * @return bool
     */
    private function inCache(): bool
    {
        $this->setTimeStartEnd(Timestep::Hour);

        $cache = Consult::query()->where([
            'location' => $this->location,
            'timezone' => $this->timezone,
            'startTime' => $this->startTime,
        ])->first();

        if (!is_null($cache)) {
            $this->result = $cache;
        }

        return !is_null($cache);
    }

    /**
     * Realiza a consulta na API
     *
     * @return void
     * @throws Exception
     */
    private function consult(): void
    {
        $days = $this->consultDays();
        $hours  = $this->consultHours();

        $days = json_decode(json_encode($days), true);
        $hours = json_decode(json_encode($hours), true);

        $today = $this->generateToday($days);
        $nextDays = $this->generateNextDays($days, $hours);

        $this->saveConsult($today, $nextDays);
    }

    /**
     * Consulta os próximos 15 dias
     *
     * @return array
     * @throws Exception
     */
    private function consultDays(): array
    {
        $this->setTimeStartEnd(Timestep::Day);
        $url = $this->url(Timestep::Day);
        $response = Http::get($url);

        if ($response->status() != 200) {
           $this->saveErrorResponse($response);
           throw new Exception('Erro ao consultar a previsão para os próximos dias');
        }

        $intervals = Arr::get($response->json(), 'data.timelines.0.intervals');

        return $this->calculateVelocityWind($intervals);
    }

    /**
     * Consultas as próximas 108 horas
     *
     * @return array
     * @throws Exception
     */
    private function consultHours(): array
    {
        $this->setTimeStartEnd(Timestep::Hour);
        $url = $this->url(Timestep::Hour);

        $response = Http::get($url);

        if ($response->status() != 200) {
            $this->saveErrorResponse($response);
            throw new Exception('Erro ao consultar a previsão para as próximas horas');
        }

        $intervals = Arr::get($response->json(), 'data.timelines.0.intervals');

        return $this->calculateVelocityWind($intervals);
    }

    /**
     * Retorna os campos que devem ser retornados na consulta
     *
     * @return Collection
     */
    private function fields(): Collection
    {
        return new Collection([
            new Field('precipitationIntensity', FieldComparison::Bigger),
            new Field('windDirection', FieldComparison::Frequency),
            new Field('windSpeed', FieldComparison::Bigger),
            new Field('precipitationProbability', FieldComparison::Bigger),
            new Field('precipitationType', FieldComparison::Frequency),
            new Field('temperature', FieldComparison::Bigger),
            new Field('temperatureApparent', FieldComparison::Bigger),
            new Field('temperatureMax', FieldComparison::Bigger),
            new Field('temperatureMin', FieldComparison::Smaller),
            new Field('weatherCode', FieldComparison::Frequency),
            new Field('humidity', FieldComparison::Smaller),
        ]);
    }

    /**
     * Define o intervalo da consulta com o máximo de tempo disponível.
     *
     * @param Timestep $timestep
     * @return void
     */
    private function setTimeStartEnd(Timestep $timestep): void
    {
        $now = Carbon::now()->setTimezone($this->timezone);
        $startDay = $now->copy()->startOfDay();

        if ($startDay->diffInHours($now, true) < 6) {
            $now->startOfDay();
        } else {
            $now->subHours(6);
        }

        $this->startTime = $now->copy();

        $this->endTime = match ($timestep) {
            Timestep::Hour => $now->copy()->addHours(96),
            Timestep::Day => $now->copy()->addDays(14),
        };
    }

    /**
     * Trata a resposta e armazena na propriedade $result
     *
     * @param array $today
     * @param array $nextDays
     * @return void
     */
    private function saveConsult(array $today, array $nextDays): void
    {
        $create = Consult::query()->create([
            'location'   => $this->location,
            'timezone'   => $this->timezone,
            'startTime'  => $this->startTime,
            'endTime'    => $this->endTime,
            'today'      => $today,
            'next_days'  => $nextDays,
            'created_at' => now(),
        ]);

        if ($create instanceof Consult) {
            $this->result = $create;
        }
    }

    /**
     * Separa os dados de hoje dos dados de dias consultados
     *
     * @param array $intervals
     * @return array
     */
    private function generateToday(array $intervals): array
    {
        return Arr::first($intervals);
    }

    /**
     * Coloca as informações de cada hora nos seus respectivos dias
     *
     * @param array $days
     * @param array $hours
     * @return array
     */
    private function generateNextDays(array $days, array $hours): array
    {
        foreach ($hours as $hour) {
            $carbonHour = Carbon::make($hour['startTime']);

            foreach ($days as &$day) {
                $startDay = Carbon::make($day['startTime'])->startOfDay();
                $endDay = $startDay->copy()->endOfDay();

                if ($carbonHour->betweenIncluded($startDay, $endDay)) {
                    $day['hours'][] = $hour;
                }
            }
        }

        return $days;
    }

    /**
     * Monta a URL para onde deve ser lançada a requisição da API
     *
     * @param Timestep $timestep
     * @return string
     */
    private function url(Timestep $timestep): string
    {
        $fields = $this->fields()->implode('name', ',');

        $params = [
            "apikey"    => $this->apiKey,
            "location"  => $this->location,
            "fields"    => $fields,
            "startTime" => $this->startTime->toISOString(),
            "endTime"   => $this->endTime->toISOString(),
            "timesteps" => $timestep->value,
            "units"     => 'metric',
        ];

        $queryString = http_build_query($params);

        return $this->baseUrl . '?' . $queryString;
    }

    /**
     * Converte a velocidade do vento de m/s para km/h
     *
     * @param array $intervals
     * @return array
     */
    private function calculateVelocityWind(array $intervals): array
    {
        foreach ($intervals as &$interval) {
            $interval['values']['windSpeed'] *= 3.6;
        }

        return $intervals;
    }

    private function saveErrorResponse(Response $response)
    {
        ErrorLog::query()->create([
            'http_status' => $response->status(),
            'request'     => $response->handlerStats()['url'],
            'response'    => $response->json(),
            'headers'     => $response->headers(),
            'created_at'  => now(),
        ]);
    }

    /**
     * Retorna os dados de hoje, contando com dados de horas a hora
     *
     * @return array
     */
    public function today(): array
    {
        return $this->result->today;
    }

    /**
     * Retorna os dados dos próximos dias, contando com dados de hora a horas para os primeiro 4 dias
     *
     * @return array
     */
    public function nextDays(): array
    {
        return $this->result->next_days;
    }
}
