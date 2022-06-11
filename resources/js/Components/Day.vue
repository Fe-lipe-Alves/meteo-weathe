<template>

    <div class="w-2/12 lg:w-44 px-0.5 lg:p-1 lg:px-0 last:hidden lg:last:block">
        <div class="bg-grayish-white-200 pt-5 pb-3 px-1 lg:px-5 rounded-md h-full text-center lg:text-left">

            <p class="text-center text-sm" :title="day.values.dayOfWeek">
                {{ day.values.dayOfWeek.substring(0, 3).toUpperCase() }}
            </p>

            <div class="flex justify-center pt-3">
                <img
                    :src="day.values.weatherIcon"
                    :title="day.values.weatherCodeDescription"
                    class="w-12"
                >
            </div>

            <div class="flex justify-center py-4 flex-col lg:flex-row">
                <div class="w-full lg:w-6/12">
                    <span class="text-xl lg:text-2xl mr-2">
                        {{ Math.trunc(day.values.temperatureMax) }}°C
                    </span>
                </div>
                <div class="w-full lg:w-6/12">
                    <span class="text-md lg:text-lg ml-2 text-gray-700">
                        {{ Math.trunc(day.values.temperatureMin) }}°C
                    </span>
                </div>
            </div>

            <p class="text-xs">
                {{ day.values.weatherCodeDescription }}
            </p>
        </div>
    </div>

</template>

<script>
export default {
    name: "Day",
    props: {
        day: Object,
    }
}
</script>

<style scoped>

</style>
