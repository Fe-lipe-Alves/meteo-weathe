<template>

    <section id="today" class="w-full">
        <div class="w-11/12 mx-auto">

            <!-- Topo-->
            <div class="flex flex-wrap">
                <div class="w-6/12 lg:w-auto lg:inline order-1 whitespace-nowrap text-shadow">
                    <h1 class="text-lg font-roboto self-end">Meteo Weather</h1>
                </div>
                <div class="w-full lg:w-auto order-3 lg:order-2 grow-[2] lg:mt-0 mt-8 text-shadow">
                    <h2 class="text-4xl text-center">Presidente Prudente</h2>
                </div>
                <div class="w-6/12 lg:w-auto order-2 lg:order-3 text-shadow">
                    <h3 class="text-md font-roboto self-end text-right">28/01/2022</h3>
                </div>
            </div>
            <!-- Fim Topo -->


            <div class="w-full">

                <!-- Icone -->
                <div class="w-full flex justify-center pt-8">
                    <img src="/images/icons/weather/42081_rain_partly_cloudy_large@2x.webp" alt="Noite chuvosa" class="w-32">
                </div>
                <!-- Fim Icone -->

                <!-- Temperatura -->
                <div class="w-full flex justify-center pt-8">
                    <div class="bg-grayish-white-100 py-1 px-8 rounded-full">
                        <span class="text-4xl mr-3">22°C</span>
                        <span class="text-3xl ml-3 text-gray-700">13°C</span>
                    </div>
                </div>
                <!-- Fim Temperatura -->

                <!-- Detalhes -->
                <div class="w-full flex justify-center pt-8">
                    <div id="details" class="w-full lg:w-6/12 bg-grayish-white-200/25 flex justify-between py-2 px-2 lg:px-10 rounded-md font-semibold text-md text-gray-900">
                        <p class="w-4/12 text-shadow text-center">Sensação térmica 21°C</p>
                        <p class="w-4/12 text-shadow text-center border-x ">Vento 5 km/h</p>
                        <p class="w-4/12 text-shadow text-center">Umidade 83%</p>
                    </div>
                </div>
                <!-- Fim Detalhes -->

            </div>

        </div>
    </section>

</template>

<script>
export default {
    name: "Today"
}
</script>

<style scoped>

</style>
