<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IpInfo
{
    private const BASE_URL = 'http://ip-api.com/json/{ip}';

    /**
     * Realiza a consulta dos dados do endereço IP
     *
     * @param string $ip
     * @return array
     */
    public static function consultIp(string $ip): array
    {
        $url = str_replace('{ip}', $ip, self::BASE_URL);

        return Http::get($url)->json();
    }
}
