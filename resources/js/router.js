import { createRouter, createWebHistory } from 'vue-router';
import Login from './pages/Auth/Login.vue';
import Register from './pages/Auth/Register.vue';
import Profile from './pages/Auth/Profile.vue';
import Dashboard from './pages/Modules/Dashboard.vue';
import ProjectIndex from './pages/Modules/projects/Index.vue';
import ProjectCreate from './pages/Modules/projects/Create.vue';
import ProjectEdit from './pages/Modules/projects/Edit.vue';
import ProjectShow from './pages/Modules/projects/Show.vue';
import AssessmentIndex from './pages/Modules/Assessments/Index.vue';
import AssessmentHistory from './pages/Modules/Assessments/History.vue';
import AssessmentReview from './pages/Modules/Assessments/Review.vue';
import UserIndex from './pages/Modules/Master/Users.vue';
import DocumentTypeIndex from './pages/Modules/Master/DocumentTypes.vue';
import CertificatePreview from './pages/Modules/Exports/CertificatePreview.vue';

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: Login, meta: { public: true } },
  { path: '/register', component: Register, meta: { public: true } },
  { path: '/profile', component: Profile },
  { path: '/dashboard', component: Dashboard },
  { path: '/projects', component: ProjectIndex },
  { path: '/projects/create', component: ProjectCreate },
  { path: '/projects/:id/edit', component: ProjectEdit },
  { path: '/projects/:id', component: ProjectShow },
  { path: '/assessments', component: AssessmentIndex },
  { path: '/assessments/history', component: AssessmentHistory },
  { path: '/assessments/:id/review', component: AssessmentReview },
  { path: '/master/users', component: UserIndex },
  { path: '/master/document-types', component: DocumentTypeIndex },
  { path: '/exports/projects/:id/certificate/preview', component: CertificatePreview },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const hasToken = Boolean(localStorage.getItem('siperdok_token'));

  if (!to.meta.public && !hasToken) {
    return '/login';
  }

  if (to.meta.public && hasToken) {
    return '/dashboard';
  }

  return true;
});

export default router;
