<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\Geoapify;
use App\Services\IpInfo;
use App\Services\Tomorrow;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

            if ($request->has('lat', 'lon', 'city')) {
                $response = Tomorrow::consultCities($request->city);
                $data = Arr::get($response, 'data.cities.0');
                $timezone = $data['timezone'];
                $latLon = Arr::only($data, ['lat', 'lng']);
                $city = $data['name'];
            } else {
//                $ipClient = $request->ip();
                $ipClient = '186.193.112.12';
                $consultIp = IpInfo::consultIp($ipClient);
                $latLon = Arr::only($consultIp, ['lat', 'lon']);
                $timezone = $consultIp['timezone'];
                $city = $consultIp['city'];
            }
            $latLon = implode(',', $latLon);

            $tomorrow = new Tomorrow($latLon, $timezone);

            $now = $tomorrow->now();

            $today = $tomorrow->today();
            $today['city'] = $city;

            $nextDays = $tomorrow->nextDays(7);

            $nextHours = $tomorrow->nextHours(24);

            $image = Image::query()->where()->first();

            $imageBackground = asset($image->path);

            return Inertia::render('Home', [
                'dataNow'       => $now,
                'dataToday'     => $today,
                'dataNextDays'  => $nextDays,
                'dataNextHours' => $nextHours,
                'imageBackground' => $imageBackground,
                'links'         => [
                    'search_complete' => route('search-complete')
                ]
            ]);
        } catch (Exception) {
            return Inertia::render('Error');
        }
    }

    public function searchComplete(Request $request)
    {
        $response = Geoapify::consult($request->search);

        return response()->json([
            'success' => true,
            'cities' => $response,
        ]);
    }
}
