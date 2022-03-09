<?php

namespace App\Http\Controllers;

use App\Services\IpInfo;
use App\Services\Tomorrow;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PHPUnit\Exception;

class HomeController extends Controller
{
    /**
     * Trata as consultas de API e rendezira a tela principal
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request): Response
    {
        try {
            //        $ipClient = $request->ip();
            $ipClient = '186.193.112.12';
            $consultIp = IpInfo::consultIp($ipClient);
            $latLon = $consultIp['lat'] .','. $consultIp['lon'];

            $tomorrow = new Tomorrow($latLon, $consultIp['timezone']);

            $now = $tomorrow->now();
            $today = $tomorrow->today();
            $now['location'] = $consultIp['city'];
            $nextDays = $tomorrow->nextDays(7);

            return Inertia::render('Home', [
                'dataNow'      => $now,
                'dataToday'    => $today,
                'dataNextDays' => $nextDays,
            ]);
        } catch (Exception) {
            return Inertia::render('Error');
        }
    }
}
