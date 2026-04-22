import { createRouter, createWebHistory } from 'vue-router';
import GeneratedReportsList from '../pages/GeneratedReportsList.vue';
import CreateGeneratedReport from '../pages/CreateGeneratedReport.vue';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/generated-reports',
    name: 'generated-reports-list',
    component: GeneratedReportsList,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/generated-reports/create',
    name: 'generated-reports-create',
    component: CreateGeneratedReport,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/generated-reports');
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return next('/generated-reports');
  }

  return next();
});

export default router;

