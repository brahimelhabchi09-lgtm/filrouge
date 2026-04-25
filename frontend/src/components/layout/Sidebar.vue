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
  width: var(--sidebar-width, 260px);
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #050505;
  border-right: 1px solid rgba(124,58,237,0.12);
  position: relative;
}

.sidebar-container::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(124,58,237,0.5), rgba(6,182,212,0.5), transparent);
}

/* Header */
.sidebar-header {
  padding: 1.25rem 1.125rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.04);
}

.logo { display: flex; align-items: center; gap: 0.75rem; }

.logo-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  color: white;
  box-shadow: 0 0 20px var(--primary-glow), inset 0 1px 0 rgba(255,255,255,0.15);
  flex-shrink: 0;
  animation: pulse-glow 3s ease-in-out infinite;
}

.logo-text { display: flex; flex-direction: column; line-height: 1; }

.logo-title {
  font-size: 1rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  background: linear-gradient(135deg, #fff 0%, var(--primary-light) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-badge {
  font-size: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.25em;
  color: var(--accent);
  margin-top: 0.2rem;
  font-weight: 600;
}

/* Nav */
.sidebar-nav {
  flex: 1;
  padding: 1rem 0.75rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.nav-section-title {
  font-size: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: rgba(255,255,255,0.2);
  padding: 0 0.5rem;
  margin-bottom: 0.375rem;
  display: block;
  font-weight: 700;
}

.nav-list { list-style: none; display: flex; flex-direction: column; gap: 2px; }

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 0.75rem;
  color: rgba(255,255,255,0.35);
  text-decoration: none;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  position: relative;
  font-size: 0.875rem;
  font-weight: 500;
  border: 1px solid transparent;
}

.nav-link:hover {
  color: rgba(255,255,255,0.8);
  background: rgba(255,255,255,0.04);
  border-color: rgba(255,255,255,0.06);
}

.nav-link.active {
  color: white;
  background: linear-gradient(135deg, rgba(124,58,237,0.2) 0%, rgba(6,182,212,0.08) 100%);
  border-color: rgba(124,58,237,0.25);
  box-shadow: 0 0 20px rgba(124,58,237,0.1), inset 0 1px 0 rgba(255,255,255,0.05);
}

.nav-link.active::before {
  content: '';
  position: absolute;
  left: 0; top: 50%;
  transform: translateY(-50%) scaleY(1);
  width: 2px; height: 18px;
  background: linear-gradient(180deg, var(--primary-light), var(--accent));
  border-radius: 0 2px 2px 0;
  box-shadow: 0 0 10px var(--primary-glow);
  animation: bar-in 0.2s ease;
}

.nav-icon { opacity: 0.5; transition: all var(--transition-fast); }
.nav-link:hover .nav-icon { opacity: 0.9; }
.nav-link.active .nav-icon { opacity: 1; filter: drop-shadow(0 0 6px var(--primary-light)); }

.nav-label { flex: 1; }

.nav-badge {
  background: var(--primary);
  color: white;
  font-size: 0.55rem;
  font-weight: 700;
  padding: 0.1rem 0.4rem;
  border-radius: var(--radius-full);
  box-shadow: 0 0 8px var(--primary-glow);
}

/* Footer */
.sidebar-footer {
  padding: 0.875rem 1rem;
  border-top: 1px solid rgba(255,255,255,0.04);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.user-info { display: flex; align-items: center; gap: 0.625rem; min-width: 0; }

.user-avatar-wrap { position: relative; flex-shrink: 0; }

.user-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.625rem; font-weight: 800; color: white;
  border: 1px solid rgba(124,58,237,0.4);
  box-shadow: 0 0 12px rgba(124,58,237,0.3);
}

.online-dot {
  position: absolute; bottom: -2px; right: -2px;
  width: 8px; height: 8px;
  background: var(--success);
  border-radius: 50%;
  border: 2px solid #050505;
  box-shadow: 0 0 8px var(--neon-green-glow);
  animation: neon-pulse 2s ease-in-out infinite;
}

.user-details { display: flex; flex-direction: column; min-width: 0; line-height: 1.2; }

.user-name {
  font-size: 0.8125rem; font-weight: 600; color: var(--text-main);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.user-role {
  font-size: 0.5625rem; color: var(--text-muted);
  text-transform: uppercase; letter-spacing: 0.08em;
}

.logout-btn {
  width: 28px; height: 28px;
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,0.25);
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
  border: 1px solid transparent;
  flex-shrink: 0;
}

.logout-btn:hover {
  background: rgba(244,63,94,0.1);
  color: var(--error);
  border-color: rgba(244,63,94,0.2);
  box-shadow: 0 0 12px rgba(244,63,94,0.2);
}
</style>