<template>

    <div id="global" class="h-full lg:pt-16 pt-8 overflow-auto">

        <!-- Topo -->
        <Today/>

        <!-- Próximos Dias -->
        <section class="w-11/12 mx-auto flex justify-between pt-14 flex-wrap">
            <Day v-for="day in days" :key="day.index"/>
        </section>
        <!-- Próximos Dias -->

        <!-- Hora a Hora -->
        <Timeline/>

        <!-- Rodape -->
        <Footer/>
    </div>

</template>

<script>
import Today from "../Components/Today";
import Day from "../Components/Day";
import Timeline from "../Components/Timeline";
import Footer from "../Components/Footer";

export default {
    components: {Footer, Timeline, Day, Today},
    props: {
        name: String,
        days: {
            type: Array,
            default: [1,2,3,4,5,6],
        },
    }
}
</script>

<style>
    body {
        background-image: linear-gradient(0deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.2)),
                          url('/images/backgrounds/background-005.webp');
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
    }
</style>
