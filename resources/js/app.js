import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress'
import { dragscrollNext } from "vue-dragscroll";

InertiaProgress.init()

createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })

        app.directive('dragscroll', dragscrollNext);
        app.use(plugin)
        app.mount(el)
    },
})
