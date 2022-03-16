import { createApp, h } from 'vue'
import { createInertiaApp, Link } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress'
import { dragscrollNext } from "vue-dragscroll"

const cors = require('cors')

InertiaProgress.init()

createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .directive('dragscroll', dragscrollNext)
            .use(plugin)
            .use(cors)
            .component('InertiaLink', Link)
            .mount(el)
    },
})
