import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import StatusBadge from './components/StatusBadge.vue';
import DecisionModal from './components/DecisionModal.vue';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`].default;
  },
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) });
    vueApp.use(plugin);
    vueApp.component('status-badge', StatusBadge);
    vueApp.component('decision-modal', DecisionModal);
    vueApp.mount(el);
  },
});
