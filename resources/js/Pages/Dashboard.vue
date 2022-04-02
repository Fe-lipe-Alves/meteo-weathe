<template>

    <div id="global" class="h-full lg:pt-16 pt-8 overflow-auto">

        <section id="today" class="w-full">
            <div class="w-11/12 mx-auto">

                <div class="flex flex-wrap relative">

                    <div class="w-6/12 lg:w-auto lg:inline order-1 whitespace-nowrap text-shadow absolute">
                        <h1 class="text-lg font-roboto self-end">Meteo Weather</h1>
                    </div>

                    <div class="w-full lg:w-auto order-3 lg:order-2 grow-[2] lg:mt-0 mt-8 text-shadow">
                        <form action="/" method="post" class="w-full">
                            <div class="w-full lg:w-[30rem] mx-auto relative">
                                <h1 class="w-full bg-transparent focus:outline-none text-3xl lg:text-4xl text-center">
                                    Imagens de Backgound
                                </h1>
                            </div>
                        </form>
                    </div>

                    <div class="w-6/12 lg:w-auto lg:inline order-1 whitespace-nowrap text-shadow absolute right-0">
                        <button
                            class="text-md font-roboto self-end border border-gray-700 text-gray-700 rounded-md px-3 py-1"
                            @click="() => { boxNewImage = true }"
                        >
                            Nova imagem
                        </button>
                    </div>

                </div>

            </div>
        </section>

        <section class="w-11/12 mx-auto flex pt-14 flex-wrap">
            <div class="w-4/12 p-1" v-for="image in images" :key="image">
                <div class="h-full ">
                    <img
                        :src="'http://127.0.0.1:8000/images/backgrounds/background-00'+image+'.webp'"
                        class="w-full h-full object-cover rounded-md overflow-hidden"
                    >
                </div>
            </div>
        </section>

        <aside
            class="w-full h-full absolute top-0 left-0 bg-transparent flex justify-center items-center"
            v-if="boxNewImage"
        >
            <div id="modal" class="w-8/12 h-4/5 bg-white shadow-2xl rounded-xl overflow-auto">
                <form action="#" method="post" class="w-full px-16 pt-8 pb-0">
                    <div class="flex justify-between items-center border-b">
                        <h5>
                            Nova imagem de background
                        </h5>
                        <button
                            type="button"
                            class="text-3xl px-2"
                            title="Fechar"
                            @click="() => { boxNewImage = false }"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="w-full py-5">
                        <h6>Clima:</h6>
                        <ul class="my-3 flex flex-wrap">
                            <li
                                v-for="weather in weathers" :key="weather.name"
                                class="text-xs border border-purple-800 rounded-full inline-block m-1 cursor-default"
                                :class="weather.active ? 'bg-purple-300 text-black' : 'hover:bg-purple-100 text-gray-700'"
                            >
                                <input
                                    type="checkbox"
                                    name="weather[]"
                                    class="hidden"
                                    :value="weather.name"
                                    v-model="weather.active"
                                    :id="'weather_'+weather.name"
                                    required
                                >
                                <label :for="'weather_'+weather.name" class=" px-2 py-1 inline-block rounded-full">
                                    {{ weather.value }}
                                </label>
                            </li>
                        </ul>
                    </div>

                    <div class="w-full p-2">
                        <h6 class="inline-flex mr-3">Período:</h6>
                        <ul class="inline-flex flex-wrap">
                            <li
                                v-for="period in periods" :key="period.value"
                                class="text-xs border border-r-0 border-purple-800 first:rounded-l-full last:rounded-r-full last:border-r inline-block cursor-default w-14 text-center"
                                :class="periodActive === period.value ? 'bg-purple-300 text-black' : 'hover:bg-purple-100 text-gray-700'"
                            >
                                <input
                                    type="radio"
                                    name="period"
                                    class="hidden"
                                    :value="period.value"
                                    :id="'period_'+period.value"
                                    @change="() => { periodActive = period.value }"
                                    required
                                >
                                <label :for="'period_'+period.value" class=" px-2 py-1 inline-block rounded-full">
                                    {{ period.name }}
                                </label>
                            </li>
                        </ul>
                    </div>

                    <div class="w-full p-2">
                        <h6 class="inline-flex mr-3">Imagem:</h6>
                        <input type="file" id="file_image" class="hidden" @change="changeInput" required>
                        <label
                            for="file_image"
                            class="text-xs px-2 py-1 border border-gray-400 rounded-md inline-block m-1 cursor-default hover:bg-purple-50 text-gray-700"
                        >
                            Selecionar imagem
                        </label>
                    </div>

                    <div class="w-full flex justify-center max-h-80 bg-gray-50 py-2" v-if="!!imagePreview.data">
                        <img :src="imagePreview.data" alt="Previsão da imagem" class="object-contain">
                    </div>

                    <div class="flex justify-center items-center border-t py-2 mt-8">
                        <button type="submit" class="rounded-lg text-md px-4 py-2 bg-gray-500 text-white">Enviar</button>
                    </div>
                </form>

            </div>
        </aside>

    </div>

</template>

<script>
export default {
    name: "Dashboard",
    props: {
        images: {
            type: Array,
            default: [1,2,3,4,5,1,2,3,4,5,1,2,3,4,5]
        },
        weathers: {
            type: Array,
        },
        periods: {
            type: Array,
            default: [
                {name: 'Dia', value: 1},
                {name: 'Noite', value: 2},
            ]
        },
    },
    data() {
        return {
            boxNewImage: false,
            imagePreview: {
                data: null,
                url: null,
            },
            periodActive: null,
        }
    },
    methods: {
        changeInput(input) {
            let file = input.target.files[0];
            // this.imagePreview.url = URL.createObjectURL(this.imagePreview.data);
            // console.log(this.previewImage)

            if (file) {
                let reader = new FileReader
                reader.onload = e => {
                    this.imagePreview.data = e.target.result
                }
                reader.readAsDataURL(file)
                // this.$emit('input', file)
            }
        },
    },
    mounted() {
    }
}
</script>

<style scoped>

</style>
