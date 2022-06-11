<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Support\Enums\Period;
use App\Support\Enums\Weather;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class ImageController extends Controller
{
    public function images()
    {
        $weathers = array_map(function ($item) {
            $item = (array)$item;
            $item['active'] = false;

            return $item;
        }, Weather::cases());

        $response['weathers'] = Arr::where($weathers, function($item) {
            return !empty($item['value']);
        });

        if (Session::has('fresh.success')) {
            $response['fresh']['success'] = Session::get('fresh.success');
        }

        if (Session::has('fresh.error')) {
            $response['fresh']['error'] = Session::get('fresh.error');
        }

        $response['images'] = Image::query()->with('weathers')->get();


        return Inertia::render('Dashboard', $response);
    }

    public function newImage(Request $request)
    {
        $request->validate(
            [
                'weather' => ['required', 'array'],
                'period'  => ['required', new Enum(Period::class)],
                'image'   => ['nullable', 'file', 'image', Rule::requiredIf(is_null($request->get('id')))],
                'id'      => ['nullable', 'exists:images,id'],
            ],
            [
                'weather.required'       => 'Informe ao menos um clima',
                'weather.array'          => 'Formato não encontrado',
                'period.required'        => 'Informe o período',
                'period.array'           => 'Formato não encontrado',
                'image.required_without' => 'Insira a imagem',
                'image.file'             => 'A imagem deve ser um arquivo de imagem válido',
                'image.image'            => 'A imagem deve ser um arquivo de imagem válido',
                'image.uploaded'         => 'Ocorreu um erro ao enviar a imagem',
            ]
        );

        if ($request->id) {
            $image = Image::query()->find($request->id);
        } else {
            $image = new Image();
        }

        $image->period = $request->period;

        if (!empty($request->image) && $request->image instanceof UploadedFile) {
            $image->path = $request->image->storePublicly('/public/background');
        }

        $weathersIds = \App\Models\Weather::query()
            ->select('id')
            ->whereIn('code', $request->weather)
            ->pluck('id')
            ->toArray();

        try {
            DB::beginTransaction();

            $image->save();
            $image->weathers()->sync($weathersIds);
            $image->save();

            DB::commit();

            return redirect()->route('dashboard')->with('fresh.success', 'Imagem cadastrada com sucesso');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('fresh.error', 'Falha ao cadastrar imagem');
        }
    }

    public function delete(Image $image)
    {
        $image->weathers()->detach();
        $image->delete();
        return redirect()->route('dashboard');
    }
}
