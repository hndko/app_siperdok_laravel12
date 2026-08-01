import './bootstrap';
import { createApp } from 'vue';
import StatusBadge from './components/StatusBadge.vue';
import DecisionModal from './components/DecisionModal.vue';

const app = createApp({});

app.component('status-badge', StatusBadge);
app.component('decision-modal', DecisionModal);

app.mount('#vue-app');
