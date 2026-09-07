import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { formatDateTime, formatDate, formatTime } from '@/Utils/date'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    let page = pages[`./Pages/${name}.vue`]

    if (!name.startsWith('Auth/')) {
      page.default.layout = page.default.layout || AuthenticatedLayout
    }

    return page
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)

    app.config.globalProperties.$formatDateTime = formatDateTime
    app.config.globalProperties.$formatDate = formatDate
    app.config.globalProperties.$formatTime = formatTime

    app.mount(el)
  },
  progress: {
    color: '#09090b',
    showSpinner: true,
  },
})
