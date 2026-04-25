import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../pages/auth/Login.vue';
import DashboardLayout from '../components/layout/DashboardLayout.vue';
import AdminDashboard from '../pages/admin/AdminDashboard.vue';
import AdminCreateUser from '../pages/admin/AdminCreateUser.vue';
import AdminCategories from '../pages/admin/AdminCategories.vue';
import BDEDashboard from '../pages/bde/BDEDashboard.vue';
import BDEMeetings from '../pages/bde/BDEMeetings.vue';
import StudentDashboard from '../pages/student/StudentDashboard.vue';
import StudentCreateReport from '../pages/student/StudentCreateReport.vue';
import TeacherDashboard from '../pages/teacher/TeacherDashboard.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: () => {
          const auth = useAuthStore();
          const roleRoutes: Record<string, string> = {
            ADMIN: '/admin/dashboard',
            BDE: '/bde/dashboard',
            TEACHER: '/teacher/dashboard',
            STUDENT: '/student/dashboard',
          };
          return roleRoutes[auth.userRole] || '/login';
        },
      },
      {
        path: 'admin/dashboard',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { requiresRole: 'admin' }
      },
      {
        path: 'admin/create-user',
        name: 'admin-create-user',
        component: AdminCreateUser,
        meta: { requiresRole: 'admin' }
      },
      {
        path: 'admin/categories',
        name: 'admin-categories',
        component: AdminCategories,
        meta: { requiresRole: 'admin' }
      },
      {
        path: 'bde/dashboard',
        name: 'bde-dashboard',
        component: BDEDashboard,
        meta: { requiresRole: 'bde' }
      },
      {
        path: 'bde/meetings',
        name: 'bde-meetings',
        component: BDEMeetings,
        meta: { requiresRole: 'bde' }
      },
      {
        path: 'teacher/dashboard',
        name: 'teacher-dashboard',
        component: TeacherDashboard,
        meta: { requiresRole: 'teacher' }
      },
      {
        path: 'student/dashboard',
        name: 'student-dashboard',
        component: StudentDashboard,
        meta: { requiresRole: 'student' }
      },
      {
        path: 'create-report',
        name: 'create-report',
        component: StudentCreateReport,
        meta: { requiresRole: 'student' }
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresGuest && auth.isAuthenticated) {
    const roleRoutes = {
      'ADMIN': '/admin/dashboard',
      'BDE': '/bde/dashboard',
      'TEACHER': '/teacher/dashboard',
      'STUDENT': '/student/dashboard'
    };
    return next(roleRoutes[auth.userRole] || '/student/dashboard');
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' });
  }

  if (to.meta.requiresRole) {
    const userRole = auth.user?.role?.toUpperCase() || '';
    const requiredRole = to.meta.requiresRole.toUpperCase();
    const roleMatch = userRole === requiredRole;
    
    if (!roleMatch) {
      console.log('Role mismatch:', { userRole, requiredRole });
      const roleRoutes = {
        'ADMIN': '/admin/dashboard',
        'BDE': '/bde/dashboard',
        'TEACHER': '/teacher/dashboard',
        'STUDENT': '/student/dashboard'
      };
      return next(roleRoutes[auth.userRole] || '/student/dashboard');
    }
  }

  return next();
});

export default router;
