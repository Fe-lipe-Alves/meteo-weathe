<template>

    <section class="w-11/12 mx-auto flex justify-between pt-14">

        <div class="bg-grayish-white-100 w-full pt-5 pb-3 px-5 rounded-md">
            <h4 class="text-2xl">Hoje <span class="text-lg"> - A cada hora</span></h4>

            <div class="w-full overflow-auto whitespace-nowrap px-6">
                <div class="inline-flex py-6" v-for="hour in hours" :key="hour.index">
                    <div class="inline-flex items-center flex-col">
                        <div class="dashed flex flex-1 justify-center">
                            <svg>
                                <rect x="0" y="0" height="100%"></rect>
                            </svg>
                        </div>
                        <div class="text-xs mt-3">22:00</div>
                    </div>
                    <div class="details flex flex-col justify-center text-center py-4 px-4">
                        <div class="flex flex-col justify-center my-2">
                            <img src="/images/icons/weather/42081_rain_partly_cloudy_large.webp" alt="Dia chuvoso" class="w-12">
                        </div>

                        <div class="text-sm my-1 flex">
                            <img src="/images/icons/weather/rain.svg" alt="Probabilidade de chuva" class="w-4 mr-1">
                            85%
                        </div>
                        <div class="text-sm my-1 mb-5 flex">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5.81 10.124" class="temperature-min-max__icon--K4X9"><path d="M3.401 9h-1V0h1z"></path><path d="M2.901 10.124l-2.9-3.873.8-.6 2.1 2.806L5.013 5.65l.8.6z"></path></svg>
                            15 km/h
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>

</template>

<script>
export default {
    name: "Timeline",
    props: {
        hours: {
            type: Array,
            default: [1,2,3,4,5,6,7,8,9,10,11]
        }
    },
}
</script>

<style scoped>
    .dashed svg, .dashed svg rect {
        flex: 1;
        width: 1px;
        fill: transparent;
    }
    .dashed svg rect {
        stroke: #898989;
        stroke-width: 4;
        transition: all 500ms;
        stroke-dasharray: 3;
        stroke-dashoffset: 3;
    }
</style>
