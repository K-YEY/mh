import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

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

    // register shadcn-style UI wrappers (lightweight)
    try {
      // eslint-disable-next-line global-require, import/no-unresolved
      const UI = require('./Components/UI').default
      app.use(UI)
    } catch (e) {
      // noop if components not present or require fails
      // eslint-disable-next-line no-console
      console.warn('UI wrappers not registered:', e && e.message)
    }

    app.mount(el)
  },
  progress: {
    color: '#09090b',
    showSpinner: true,
  },
})
