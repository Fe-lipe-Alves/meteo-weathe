<template>

    <div id="global" class="h-full lg:pt-16 pt-8 overflow-auto" :style="'--image-background: url(' + imageBackground + ')'">

        <!-- Topo -->
        <Today :data-now="dataNow" :data-today="dataToday"/>

        <!-- Próximos Dias -->
        <section class="w-11/12 mx-auto flex justify-between pt-14 flex-wrap">
            <Day v-for="day in dataNextDays" :key="day.index" :day="day"/>
        </section>
        <!-- Próximos Dias -->

        <!-- Hora a Hora -->
        <Timeline :dataNextHours="dataNextHours"/>

        <!-- Rodape -->
        <Footer/>
    </div>

</template>

<script>
import Today from "../Components/Today";
import Day from "../Components/Day";
import Timeline from "../Components/Timeline";
import Footer from "../Components/Footer";
import {Inertia} from "@inertiajs/inertia";

export default {
    components: {Footer, Timeline, Day, Today},
    props: {
        name: String,
        dataNow: Object,
        dataToday: Object,
        dataNextDays: Object,
        dataNextHours: Object,
        imageBackground: String
    },
    beforeMount() {
        if(!("geolocation" in navigator)) {
            return;
        }

        const geoSuccess = function (position) {
            const params = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            }

            Inertia.get(window.location.href, params, {
                preserveState: true,
            })
        };
        // navigator.geolocation.getCurrentPosition(geoSuccess);
    }
}
</script>

<style>
    root {
        --image-background: '';
    }

    #global {
        background-image: linear-gradient(0deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.2)),
                          var(--image-background);
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
    }
</style>
