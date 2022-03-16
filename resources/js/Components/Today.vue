<template>

    <section id="today" class="w-full">
        <div class="w-11/12 mx-auto">

            <!-- Topo-->
            <div class="flex flex-wrap">
                <div class="w-6/12 lg:w-auto lg:inline order-1 whitespace-nowrap text-shadow">
                    <h1 class="text-lg font-roboto self-end">Meteo Weather</h1>
                </div>
                <div class="w-full lg:w-auto order-3 lg:order-2 grow-[2] lg:mt-0 mt-8 text-shadow">
                    <form action="/" method="post" class="w-full">
                        <div class="w-full lg:w-[30rem] mx-auto relative">
                            <div
                                class="w-full py-2 px-4 text-center rounded-full"
                                title="Pesquisar cidade"
                                :class="inputFocus ? 'bg-white' : 'bg-transparent hover:bg-white/25'"
                            >
                                <input
                                    type="text"
                                    autocomplete="off"
                                    autoComplete="none"
                                    class="w-100 bg-transparent focus:outline-none text-3xl lg:text-4xl text-center"
                                    @focusin="changeFocus(true)"
                                    @focusout="changeFocus(false)"
                                    @keyup="searchComplete()"
                                    v-model="searchCity"
                                >
                            </div>

                            <div class="w-full bg-white/75 mt-1 py-2 rounded-lg absolute" v-if="citysResponse != null">
                                <ul>
                                    <li class="text-lg hover:bg-white px-4 pt-1" v-for="city in citysResponse">
                                        <inertia-link href="/" :data="{ lon: city.lon, lat: city.lat, city: city.city }">
                                            {{ city.city }}, {{ city.state_code }} - {{ city.country }}
                                        </inertia-link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="w-6/12 lg:w-auto order-2 lg:order-3 text-shadow">
                    <h3 class="text-md font-roboto self-end text-right">{{ dataToday.date }}</h3>
                </div>
            </div>
            <!-- Fim Topo -->

            <div class="w-full">

                <!-- Icone -->
                <div class="w-full flex justify-center pt-6 lg:pt-8">
                    <img
                        :src="dataNow.values.weatherIcon"
                        alt="{{ dataNow.values.weatherCodeDescription }}"
                        class="w-28 lg:w-32"
                    >
                </div>
                <!-- Fim Icone -->

                <!-- Temperatura -->
                <div class="w-full flex justify-center pt-8">
                    <div class="bg-grayish-white-100 py-1 px-8 rounded-full text-center">
                        <span class="text-3xl lg:text-4xl mr-3">
                            {{ Math.trunc(dataNow.values.temperatureMax) }}°C
                        </span>
                        <br>
                        <span class="text-sm">
                            {{ dataNow.values.weatherCodeDescription }}
                        </span>
                    </div>
                </div>
                <!-- Fim Temperatura -->

                <!-- Detalhes -->
                <div class="w-full flex justify-center pt-8">
                    <div id="details" class="w-full lg:w-9/12 bg-grayish-white-200/25 flex justify-between py-2 px-2 lg:px-10 rounded-md font-semibold text-md text-gray-900">
                        <div class="w-6/12 lg:w-3/12 text-shadow text-center">
                            Sensação térmica {{ Math.trunc(dataToday.values.temperatureApparent) }}°C
                        </div>
                        <div class="w-6/12 lg:w-3/12 text-shadow text-center border-x ">
                            Vento {{ Math.trunc(dataToday.values.windSpeed) }} km/h
                        </div>
                        <div class="w-6/12 lg:w-3/12 text-shadow text-center">
                            Umidade {{ Math.trunc(dataToday.values.humidity) }}%
                        </div>
                        <div class="w-6/12 lg:w-3/12 text-shadow text-center">
                            Probabilidade de chuva {{ Math.trunc(dataToday.values.precipitationProbability) }}%
                        </div>
                    </div>
                </div>
                <!-- Fim Detalhes -->

            </div>

        </div>
    </section>

</template>

<script>
import axios from "axios"
import moment from "moment/moment";
import SearchIcon from "../Icons/SearchIcon";

export default {
    name: "Today",
    components: {SearchIcon},
    props: {
        dataNow: Object,
        dataToday: Object,
        iconStatus: {
            type: String,
            default: '0',
        },
        links: Object,
    },
    mounted() {
        this.dataToday.date = moment(this.dataToday.startTime).format('DD/MM/YYYY')

    },
    methods: {
        changeFocus: function (checked) {
            this.inputFocus = checked
            this.searchCity = this.dataToday.city

        },

        searchComplete: function() {
            const params = new URLSearchParams({
                    text: this.searchCity,
                    lang: 'pt',
                    limit: '4',
                    type: 'city',
                    format: 'json',
                    apiKey: 'efe326930e784c00b44111ef5d697490',
                }),
            url = 'https://api.geoapify.com/v1/geocode/autocomplete?' + params.toString()

            this.citysResponse = []

            axios.get(url).then((response) => {
                if (response.data.results) {
                    this.citysResponse = response.data.results
                }
            }).catch(() => {

            })
        }
    },
    data() {
        let inputFocus = false,
            searchCity = this.dataToday.city,
            citysResponse = null

        const tokenRequest = axios.CancelToken.source()

        return {
            inputFocus,
            searchCity,
            citysResponse,
            tokenRequest,
        }
    }
}
</script>

<style scoped>

</style>
