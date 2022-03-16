<?php

namespace App\Services;

use App\Models\Consult;
use App\Models\ErrorLog;
use App\Support\Enums\DayOfWeek;
use App\Support\Enums\Timestep;
use App\Support\Enums\Weather;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Tomorrow
{
    private const BASE_URL = 'https://api.tomorrow.io/v4/timelines';
    private const URL_SEARCH = 'https://weather-services.tomorrow.io/backend/v1/cities';
    private const API_KEY = 'woUIhYHlbBiFaLX311Rv8NU0QmXC0MsP';
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
     * Consulta na API de localiazador de cidades conforme o nome recebido como parâmetro
     *
     * @param string $search
     * @return array|mixed
     */
    public static function consultCities(string $search): mixed
    {
        return Http::get(self::URL_SEARCH . '?name=' . $search)->json();
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
            'startTime' => $this->startTime->setMinute(0)->setSecond(0)->setMicrosecond(0),
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
        $hours = $this->consultHours();

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
     * @param Timestep $timestep
     * @return array
     */
    private function fields(Timestep $timestep): array
    {
        $params = [
            'precipitationIntensity',
            'windDirection',
            'windSpeed',
            'precipitationProbability',
            'precipitationType',
            'temperature',
            'temperatureApparent',
            'temperatureMax',
            'temperatureMin',
            'weatherCode',
            'humidity',
        ];

        if ($timestep == Timestep::Day) {
            $params[] = 'sunriseTime';
            $params[] = 'sunsetTime';
        }

        return $params;
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
     * Coloca as informações de cada hora nos seus respectivos dias e adiciona os campos de descrição, dia da semana e
     * imagem
     *
     * @param array $days
     * @param array $hours
     * @return array
     */
    private function generateNextDays(array $days, array $hours): array
    {
        foreach ($hours as $hour) {
            $carbonHour = Carbon::make($hour['startTime'])->setTimezone($this->timezone);
            $hour['startTime'] = $carbonHour->toISOString();

            foreach ($days as &$day) {
                $startDay = Carbon::make($day['startTime'])->setTimezone($this->timezone)->startOfDay();
                $endDay = $startDay->copy()->endOfDay();

                if ($carbonHour->betweenIncluded($startDay, $endDay)) {
                    $hour['values']['weatherCodeDescription'] = Weather::get($hour['values']['weatherCode'])->value;

                    $this->identifyIcon($hour, $day);

                    $day['hours'][] = $hour;
                }

                $day['values']['weatherCodeDescription'] = Weather::get($day['values']['weatherCode'])->value;
                $day['values']['dayOfWeek'] = DayOfWeek::get($startDay->dayOfWeek)->value;

                $path = 'images/icons/weather/large/png/' . $day['values']['weatherCode'] . '0_large@2x.png';
                $day['values']['weatherIcon'] = asset($path);
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
        $fields = implode(',', $this->fields($timestep));

        $params = [
            "apikey"    => self::API_KEY,
            "location"  => $this->location,
            "fields"    => $fields,
            "startTime" => $this->startTime->toISOString(),
            "endTime"   => $this->endTime->toISOString(),
            "timesteps" => $timestep->value,
            "units"     => 'metric',
        ];

        $queryString = http_build_query($params);

        return self::BASE_URL . '?' . $queryString;
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

    public function now()
    {
        foreach ($this->result->next_days as $day) {
            if (!isset($day['hours'])) {
                continue;
            }

            foreach ($day['hours'] as $hour) {
                $startTime = Carbon::make($hour['startTime'])->setMinute(0)->setSecond(0);
                $now = Carbon::now()->setMinute(0)->setSecond(0)->setMicrosecond(0);

                if ($now->equalTo($startTime)) {
                    $this->identifyIcon($hour, $day);

                    return $hour;
                }
            }
        }

        return $this->today();
    }

    /**
     * Identifica se o icone deve simbolozar o dia ou a noite conforme o horário atual e retorna o caminho do icone
     *
     * @param array $hour
     * @param array $day
     * @return void
     */
    public function identifyIcon(array &$hour, array $day)
    {
        $sunsetTime = Carbon::make($day['values']['sunsetTime'])->setTimezone($this->timezone);
        $sunriseTime = Carbon::make($day['values']['sunriseTime'])->setTimezone($this->timezone);
        $hourTime = Carbon::make($hour['startTime'])->setTimezone($this->timezone);

        $night = $sunsetTime->diffInMinutes($hourTime, false) < 0  &&
        $sunriseTime->diffInMinutes($hourTime, false) >= 0
            ? '0' : '1';

        $path = 'images/icons/weather/large/png/' . $hour['values']['weatherCode'] . $night . '_large@2x.png';

        if (!file_exists(public_path($path))) {
            $path = 'images/icons/weather/large/png/' . $hour['values']['weatherCode']  . '0_large@2x.png';
        }

        $hour['values']['weatherIcon'] = asset($path);
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
     * @param int $max
     * @return array
     */
    public function nextDays(int $max = 0): array
    {
        if ($max == 0) {
            return $this->result->next_days;
        }

        return array_slice($this->result->next_days, 0, $max);
    }

    /**
     * Retorna os dados das próximas horas, limitando ao valor recebido no parâmetro
     *
     * @param int $max
     * @return array
     */
    public function nextHours(int $max = 0): array
    {
        $hours = [];
        $count = 0;

        foreach ($this->result->next_days as $day) {
            if (isset($day['hours'])) {

                foreach ($day['hours'] as $hour) {
                    if ($count <= $max) {
                        $this->identifyIcon($hour, $day);
                        $hours[] = $hour;
                        $count++;
                    }
                }

            }
        }

        return $hours;
    }
}
