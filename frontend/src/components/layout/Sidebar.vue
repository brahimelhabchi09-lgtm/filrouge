<template>
  <div class="sidebar-container">
    <div class="sidebar-header">
      <div class="logo">
        <div class="logo-icon">
          <ShieldCheck :size="22" />
        </div>
        <div class="logo-text">
          <span class="logo-title">YOUPORTS</span>
          <span class="logo-badge">Campus</span>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <!-- Main section -->
      <div class="nav-section">
        <span class="nav-section-title">Main</span>
        <ul class="nav-list">
          <li v-for="item in mainNavItems" :key="item.path" class="nav-item">
            <router-link
              :to="item.path"
              class="nav-link"
              :class="{ active: isActive(item.path) }"
              @click="$emit('nav-click')"
            >
              <component :is="item.icon" :size="18" class="nav-icon" />
              <span class="nav-label">{{ item.label }}</span>
              <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Tools section -->
      <div v-if="toolNavItems.length > 0" class="nav-section">
        <span class="nav-section-title">Tools</span>
        <ul class="nav-list">
          <li v-for="item in toolNavItems" :key="item.path" class="nav-item">
            <router-link
              :to="item.path"
              class="nav-link"
              :class="{ active: isActive(item.path) }"
              @click="$emit('nav-click')"
            >
              <component :is="item.icon" :size="18" class="nav-icon" />
              <span class="nav-label">{{ item.label }}</span>
              <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar-wrap">
          <div class="user-avatar">{{ userInitials }}</div>
          <span class="online-dot"></span>
        </div>
        <div class="user-details">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">{{ roleLabel }}</span>
        </div>
      </div>
      <button class="logout-btn" @click="logout" title="Logout">
        <LogOut :size="16" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import {
  ShieldCheck,
  LayoutDashboard,
  PlusCircle,
  Users,
  LogOut,
  Tag,
  Video,
  FileText,
  BarChart3
} from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const role = computed(() => authStore.userRole || 'STUDENT');

const userName = computed(() => {
  const user = authStore.user;
  return user ? `${user.first_name} ${user.last_name}` : 'User';
});

const userInitials = computed(() => {
  const user = authStore.user;
  if (!user) return 'U';
  return `${(user.first_name || '')[0]}${(user.last_name || '')[0]}`.toUpperCase();
});

const roleLabel = computed(() => {
  const labels = {
    'ADMIN': 'Administrator',
    'BDE': 'BDE Member',
    'TEACHER': 'Teacher',
    'STUDENT': 'Student'
  };
  return labels[role.value] || 'User';
});

// Dashboard is always in "Main"
const mainNavItems = computed(() => {
  const dashboardPaths = {
    'ADMIN': '/admin/dashboard',
    'BDE': '/bde/dashboard',
    'TEACHER': '/teacher/dashboard',
    'STUDENT': '/student/dashboard',
  };
  return [
    { path: dashboardPaths[role.value] || '/student/dashboard', label: 'Dashboard', icon: LayoutDashboard }
  ];
});

// Role-specific tool links
const toolNavItems = computed(() => {
  const items = [];

  if (role.value === 'STUDENT') {
    items.push({ path: '/create-report', label: 'New Report', icon: PlusCircle });
  }
  if (role.value === 'BDE') {
    items.push({ path: '/bde/meetings', label: 'Meetings', icon: Video });
  }
  if (role.value === 'ADMIN') {
    items.push({ path: '/admin/create-user', label: 'Users', icon: Users });
    items.push({ path: '/admin/categories', label: 'Categories', icon: Tag });
  }

  return items;
});

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/');
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

defineEmits(['nav-click']);
</script>

<style scoped>
.sidebar-container {
  width: var(--sidebar-width, 280px);
  height: 100%;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, var(--surface) 0%, #0d1423 100%);
  border-right: 1px solid var(--glass-border);
}

/* ── Header ──────────────────────────────────── */
.sidebar-header {
  padding: 1.25rem 1.25rem 1rem;
  border-bottom: 1px solid var(--glass-border);
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-icon {
  width: 38px;
  height: 38px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 12px var(--primary-glow);
  flex-shrink: 0;
}

.logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1;
}

.logo-title {
  font-size: 1.0625rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: var(--text-main);
}

.logo-badge {
  font-size: 0.5625rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: var(--primary-light);
  margin-top: 0.25rem;
}

/* ── Nav ─────────────────────────────────────── */
.sidebar-nav {
  flex: 1;
  padding: 1rem 0.75rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.nav-section-title {
  font-size: 0.5625rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: var(--text-muted);
  padding: 0 0.625rem;
  margin-bottom: 0.5rem;
  display: block;
  font-weight: 700;
}

.nav-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  color: var(--text-muted);
  text-decoration: none;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  position: relative;
  font-size: 0.9rem;
}

.nav-link:hover {
  background: var(--glass);
  color: var(--text-secondary);
}

.nav-link.active {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.18) 0%, rgba(139, 92, 246, 0.1) 100%);
  color: var(--primary-light);
  box-shadow: inset 0 0 12px rgba(99, 102, 241, 0.08);
}

.nav-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%) scaleY(1);
  width: 3px;
  height: 22px;
  background: linear-gradient(180deg, var(--primary-light) 0%, var(--accent) 100%);
  border-radius: 0 2px 2px 0;
  box-shadow: 2px 0 8px var(--primary-glow);
  animation: bar-in 0.2s ease;
}

@keyframes bar-in {
  from { transform: translateY(-50%) scaleY(0); }
  to   { transform: translateY(-50%) scaleY(1); }
}

.nav-icon { opacity: 0.7; transition: opacity var(--transition-fast); }
.nav-link.active .nav-icon,
.nav-link:hover .nav-icon { opacity: 1; }

.nav-label { font-weight: 500; flex: 1; }

.nav-badge {
  margin-left: auto;
  background: var(--primary);
  color: white;
  font-size: 0.6rem;
  font-weight: 700;
  padding: 0.1rem 0.45rem;
  border-radius: var(--radius-full);
}

/* ── Footer ──────────────────────────────────── */
.sidebar-footer {
  padding: 1rem 1.125rem;
  border-top: 1px solid var(--glass-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.user-avatar-wrap {
  position: relative;
  flex-shrink: 0;
}

.user-avatar {
  width: 34px;
  height: 34px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.6875rem;
  font-weight: 700;
  color: white;
  border: 2px solid rgba(99, 102, 241, 0.3);
}

.online-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 9px;
  height: 9px;
  background: var(--success);
  border-radius: 50%;
  border: 2px solid var(--surface);
  box-shadow: 0 0 6px var(--success);
}

.user-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
  line-height: 1.2;
}

.user-name {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-main);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.625rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.logout-btn {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
  flex-shrink: 0;
}

.logout-btn:hover {
  background: var(--error-bg);
  color: var(--error);
}
</style>