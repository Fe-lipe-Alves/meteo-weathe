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
    private function fields(Timestep $timestep): Collection
    {
        $params = [
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
        ];

        if ($timestep == Timestep::Day) {
            $params[] = new Field('sunriseTime', FieldComparison::Frequency);
            $params[] = new Field('sunsetTime', FieldComparison::Frequency);
        }

        return new Collection($params);
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
     * Coloca as informações de cada hora nos seus respectivos dias
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
                    $hour['values']['weatherCodeDescription'] =
                        $this->weatherCodeDescription($hour['values']['weatherCode']);

                    $this->identifyIcon($hour, $day);

                    $day['hours'][] = $hour;
                }

                $day['values']['weatherCodeDescription'] = $this->weatherCodeDescription($day['values']['weatherCode']);
                $day['values']['dayOfWeek'] = $this->nameyDayOfWeek($startDay);

                $path = 'images/icons/weather/large/png/' . $day['values']['weatherCode'] . '0_large@2x.png';
                $day['values']['weatherIcon'] = asset($path);
            }
        }

        return $days;
    }

    /**
     * Retorna nome do dia da semana para a data informada
     *
     * @param Carbon $day
     * @return string
     */
    public function nameyDayOfWeek(Carbon $day): string
    {
        $weekMap = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return $weekMap[$day->dayOfWeek];
    }

    /**
     * Identiica a descrição do clima segundo o ícone
     *
     * @param int $weatherCode
     * @return string
     */
    public function weatherCodeDescription(int $weatherCode): string
    {
        $descriptions = [
            0    => '',
            1000 => 'Limpo',
            1001 => 'Nublado',
            1100 => 'Predominantemente Limpo',
            1101 => 'Parcialmente Nublado',
            1102 => 'Predominantemente nublado',
            2000 => 'Névoa',
            2100 => 'Nevoeiro Leve',
            3000 => 'Vento Leve',
            3001 => 'Vento',
            3002 => 'Strong Wind',
            4000 => 'Chuvisco',
            4001 => 'Chuva',
            4200 => 'Chuva Leve',
            4201 => 'Chuva Pesada',
            5000 => 'Neve',
            5001 => 'Rajadas',
            5100 => 'Pouca Neve',
            5101 => 'Neve Pesada',
            6000 => 'Garoa Congelante',
            6001 => 'Chuva Congelante',
            6200 => 'Chuva Leve e Congelante',
            6201 => 'Chuva Pesada e Congelante',
            7000 => 'Pelotas de Gelo',
            7101 => 'Pelotas de Gelo Pesado',
            7102 => 'Pelotas de Gelo Leves',
            8000 => 'Trovoadas',
        ];

        return $descriptions[$weatherCode];
    }

    /**
     * Monta a URL para onde deve ser lançada a requisição da API
     *
     * @param Timestep $timestep
     * @return string
     */
    private function url(Timestep $timestep): string
    {
        $fields = $this->fields($timestep)->implode('name', ',');

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
