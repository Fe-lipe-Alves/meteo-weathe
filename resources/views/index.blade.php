<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

</head>
<body class="bg-[url('/images/background-001.jpg')] bg-center bg-no-repeat bg-cover backdrop-blur-xs">

        <!-- Topo -->
        <header id="header" class="w-full py-2 bg-black/50">
            <div class="w-full lg:w-10/12 mx-auto flex justify-between text-white">
                <h1 id="title-page" class="text-lg">Meteo Weather</h1>
                <span>16/01/2022</span>
            </div>
        </header>
        <!-- Fim Topo -->

        <!-- Pesquisa -->
        <section id="search" class="w-full my-12">
            <div class="w-10/12 lg:w-6/12 mx-auto">
                <form action class="relative">
                    <input
                        type="text"
                        name="search"
                        id="search-input"
                        placeholder="Pesquisar cidade"
                        autocomplete="off"
                        class="w-full py-2 pl-5 z-0 focus:outline-none bg-white/50 placeholder-white text-black"
                    >
                    <div class="absolute top-1 right-4">
                        <img src="/images/ui/search.png" alt="Pesquisar">
                    </div>
                </form>

                <div id="search-results" class="w-full bg-white hidden">
                    <div class="w-full border-b-1 pl-5 py-2 border-b hover:bg-slate-200 cursor-pointer">
                        Presidente Prudente
                    </div>
                    <div class="w-full border-b-1 pl-5 py-2 border-b hover:bg-slate-200 cursor-pointer">
                        Quatá
                    </div>
                </div>
            </div>
        </section>
        <!-- Fim Pesquisa -->

        <!-- Detalhes de Hoje -->
        <section id="details-today" class="w-full my-24 text-white text-center">
            <div class="w-10/12 lg:w-6/12 mx-auto">
                <p id="city" class="text-2xl">Presidente Prudente, SP</p>
                <h3 id="temperature" class="text-6xl my-7">23°</h3>

                <div class="flex justify-around">
                    <span>Sensação térmica 21°</span>
                    <span>Ventos 5 km/h</span>
                    <span>Umidade 83%</span>
                </div>
            </div>
        </section>
        <!-- Detalhes de Hoje -->

        <section id="week" class="w-full">
            <div class="w-full lg:w-10/12 mx-auto flex justify-between text-white">
                @for($i=0; $i<7; $i++)
                    <div class="w-[11%] min-w-30 bg-black/50 py-2 px-5">
                        <p class="text-xs">qua 12</p>
                        <img src="/images/ui/cloud.png" alt="Nublado" class="my-2">
                        <p class="text-xs mb-2">Nublado</p>
                        <div class="flex justify-between">
                            <span class="text-2xl">32°</span>
                            <span class="text-2xl">16°</span>
                        </div>
                    </div>
                @endfor
            </div>
        </section>


<!-- Scripts -->
<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
