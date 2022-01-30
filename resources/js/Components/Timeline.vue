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
                            <img src="/images/icons/weather/42081_rain_partly_cloudy_large.png" alt="Dia chuvoso" class="w-12">
                        </div>

                        <div class="text-sm my-1">85%</div>

                        <div class="text-sm my-1 mb-5">15 km/h</div>
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
    // mounted() {
    //     const details = document.getElementsByClassName('details'),
    //         dashed = document.getElementsByClassName('dashed')
    //
    //     for (let i=0; i < dashed.length; i++) {
    //         dashed[i].querySelector('rect').setAttribute("height", details[0].offsetHeight+"px");
    //     }
    // }
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
