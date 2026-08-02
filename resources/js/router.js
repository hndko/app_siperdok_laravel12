import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: () => import('./pages/Auth/Login.vue'), meta: { public: true } },
  { path: '/register', component: () => import('./pages/Auth/Register.vue'), meta: { public: true } },
  { path: '/profile', component: () => import('./pages/Auth/Profile.vue') },
  { path: '/dashboard', component: () => import('./pages/Modules/Dashboard.vue') },
  { path: '/projects', component: () => import('./pages/Modules/projects/Index.vue') },
  { path: '/projects/create', component: () => import('./pages/Modules/projects/Create.vue') },
  { path: '/projects/:id/edit', component: () => import('./pages/Modules/projects/Edit.vue') },
  { path: '/projects/:id', component: () => import('./pages/Modules/projects/Show.vue') },
  { path: '/assessments', component: () => import('./pages/Modules/Assessments/Index.vue') },
  { path: '/assessments/history', component: () => import('./pages/Modules/Assessments/History.vue') },
  { path: '/assessments/:id/review', component: () => import('./pages/Modules/Assessments/Review.vue') },
  { path: '/master/users', component: () => import('./pages/Modules/Master/Users.vue') },
  { path: '/master/document-types', component: () => import('./pages/Modules/Master/DocumentTypes.vue') },
  { path: '/exports/projects/:id/certificate/preview', component: () => import('./pages/Modules/Exports/CertificatePreview.vue') },
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
