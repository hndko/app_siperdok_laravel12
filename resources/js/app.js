import './bootstrap';
import { createApp } from 'vue';

import StatusBadge from './components/StatusBadge.vue';
import DecisionModal from './components/DecisionModal.vue';

import DashboardPage from './pages/dashboard/DashboardPage.vue';
import ProjectIndexPage from './pages/projects/ProjectIndexPage.vue';
import ProjectShowPage from './pages/projects/ProjectShowPage.vue';

const app = createApp({});

app.component('status-badge', StatusBadge);
app.component('decision-modal', DecisionModal);

app.component('dashboard-page', DashboardPage);
app.component('project-index-page', ProjectIndexPage);
app.component('project-show-page', ProjectShowPage);

app.mount('#vue-app');
