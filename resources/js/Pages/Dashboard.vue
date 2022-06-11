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
                            @click="openModal"
                        >
                            Nova imagem
                        </button>
                    </div>

                </div>

            </div>
        </section>

        <section class="w-11/12 mx-auto flex pt-14 flex-wrap">
            <div class="w-4/12 p-1" v-for="image in images" :key="image.id">
                <div class="h-full">
                    <img
                        :src="image.path"
                        class="w-full h-full object-cover rounded-md overflow-hidden"
                    >
                </div>
            </div>

            <div class="w-full p-1" v-if="images.length === 0">
                <p class="text-center"><small>Não há imagens cadastradas.</small></p>
            </div>
        </section>

        <aside
            class="w-full h-full absolute top-0 left-0 bg-transparent flex justify-center items-center"
            v-if="boxNewImage"
        >
            <div id="modal" class="w-8/12 h-4/5 bg-white shadow-2xl rounded-xl overflow-auto">
                <form action="#" method="post" class="w-full px-16 pt-8 pb-0" @submit.prevent="submit">
                    <div class="flex justify-between items-center border-b">
                        <h5>
                            Nova imagem de background
                        </h5>
                        <button
                            type="button"
                            class="text-3xl px-2"
                            title="Fechar"
                            @click="openModal"
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
                                :class="newImage.weather.indexOf(weather.name) > -1 ? 'bg-purple-300 text-black' : 'hover:bg-purple-100 text-gray-700'"
                            >
                                <input
                                    type="checkbox"
                                    name="weather[]"
                                    class="hidden"
                                    :id="'weather_'+weather.name"
                                    :value="weather.name"
                                    v-model="newImage.weather"
                                >
                                <label :for="'weather_'+weather.name" class=" px-2 py-1 inline-block rounded-full">
                                    {{ weather.value }}
                                </label>
                            </li>
                        </ul>
                        <div v-if="newImage.errors.weather" class="text-xs text-red-500">{{ newImage.errors.weather }}</div>
                    </div>

                    <div class="w-full p-2">
                        <h6 class="inline-flex mr-3">Período:</h6>
                        <ul class="inline-flex flex-wrap">
                            <li
                                v-for="period in periods" :key="period.value"
                                class="text-xs border border-r-0 border-purple-800 first:rounded-l-full last:rounded-r-full last:border-r inline-block cursor-default w-14 text-center"
                                :class="newImage.period === period.value ? 'bg-purple-300 text-black' : 'hover:bg-purple-100 text-gray-700'"
                            >
                                <input
                                    type="radio"
                                    name="period"
                                    class="hidden"
                                    :value="period.value"
                                    :id="'period_'+period.value"
                                    v-model="newImage.period"
                                >
                                <label :for="'period_'+period.value" class=" px-2 py-1 inline-block rounded-full">
                                    {{ period.name }}
                                </label>
                            </li>
                        </ul>
                        <div v-if="newImage.errors.period" class="text-xs text-red-500">{{ newImage.errors.period }}</div>
                    </div>

                    <div class="w-full p-2">
                        <h6 class="inline-flex mr-3">Imagem:</h6>
                        <input
                            type="file"
                            id="file_image"
                            class="hidden"
                            @input="newImage.image = $event.target.files[0]"
                            @change="changeImage"
                        >
                        <label
                            for="file_image"
                            class="text-xs px-2 py-1 border border-gray-400 rounded-md inline-block m-1 cursor-default hover:bg-purple-50 text-gray-700"
                        >
                            Selecionar imagem
                        </label>
                        <div v-if="newImage.errors.image" class="text-xs text-red-500">{{ newImage.errors.image }}</div>
                    </div>

                    <div class="w-full flex justify-center max-h-80 bg-gray-50 py-2" v-if="!!previewImage">
                        <img :src="previewImage" alt="Previsão da imagem" class="object-contain">
                    </div>

                    <div class="flex justify-center items-center border-t py-2 mt-8 flex-col">
                        <button
                            type="submit"
                            class="rounded-lg text-md px-4 py-2 bg-gray-500 text-white self-end"
                            :disabled="newImage.processing"
                        >
                            Enviar
                        </button>
                    </div>
                </form>

            </div>
        </aside>

        <div class="w-full absolute top-0 flex flex-col text-center">
            <div v-if="viewMessage && !!fresh.success" class=" w-full py-1 text-xs bg-green-200">{{ fresh.success }}</div>
            <div v-if="viewMessage && !!fresh.error" class=" w-full py-1 text-xs bg-red-200">{{ fresh.error }}</div>
        </div>

    </div>

</template>

<script>
import { ref } from "vue";
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    name: "Dashboard",
    props: {
        images: {
            type: Array,
            default: []
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
        fresh: {
            type: Array,
            default: {
                success: null,
                error: null,
            }
        }
    },
    setup () {
        const newImage = useForm({
                id: null,
                weather: [],
                period: null,
                image: null
            })
        let boxNewImage = ref(false),
            previewImage = ref(''),
            viewMessage = ref(true)

        function changeImage() {
            if (newImage.image) {
                let reader = new FileReader
                reader.onload = e => {
                    previewImage.value = e.target.result
                }
                reader.readAsDataURL(newImage.image)
            }
        }

        function openModal() {
            boxNewImage.value = !boxNewImage.value;
            console.log(boxNewImage)
        }

        function submit() {
            newImage.post('/dashboard', {
                preserveScroll: true,
                onSuccess: () => {
                    boxNewImage.value = false
                    resetForm()
                }
            })
        }

        function resetForm() {
            newImage.reset()
        }

        return { newImage, boxNewImage, previewImage, viewMessage, openModal, changeImage, submit }
    },
    created () {
        setTimeout(() => {
            this.viewMessage = false
        }, 5000)
    },
    mounted() {
        console.log(this.images)
    }
}
</script>

<style scoped>

#progressBar {
    width: 100%;
    height: 30px;
    border: 1px solid black;
    border-radius: 7px;
    padding: 4px;
}

#progress {
    width: 100%;
    height: 5px;
    background-color: #4A90E2;
    border-top-left-radius: 7px;
    border-bottom-left-radius: 7px;
    border-color: #4A90E2;

    transition: .5s;
}
</style>
