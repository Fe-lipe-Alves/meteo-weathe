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
            $ipClient = '186.193.118.203';
            $consultIp = IpInfo::consultIp($ipClient);

            $tomorrow = new Tomorrow($consultIp['loc'], $consultIp['timezone']);

            $today = $tomorrow->today();
            $today['location'] = $consultIp['city'];

            $nextDays = $tomorrow->nextDays();

            return Inertia::render('Home', [
                'dataToday'    => $today,
                'dataNextDays' => $nextDays,
            ]);
        } catch (Exception) {
            return Inertia::render('Error');
        }
    }
}
