import './bootstrap';
import { createApp, h } from 'vue';
import { RouterView } from 'vue-router';
import SpaLink from './components/SpaLink.vue';
import StatusBadge from './components/StatusBadge.vue';
import DecisionModal from './components/DecisionModal.vue';
import router from './router';

const App = {
  render: () => h(RouterView),
};

createApp(App)
  .use(router)
  .component('Link', SpaLink)
  .component('status-badge', StatusBadge)
  .component('decision-modal', DecisionModal)
  .mount('#app');
