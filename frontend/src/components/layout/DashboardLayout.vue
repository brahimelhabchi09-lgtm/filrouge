<template>
  <div class="layout">
    <button class="mobile-toggle" @click="toggleSidebar" aria-label="Toggle menu">
      <Menu :size="20" />
    </button>

    <div class="sidebar-overlay" :class="{ active: sidebarOpen }" @click="closeSidebar"></div>

    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <sidebar-component @nav-click="closeSidebar" />
    </aside>

    <div class="main-wrapper">
      <!-- Top nav bar -->
      <header class="top-nav">
        <div class="breadcrumb">
          <span class="breadcrumb-root">YouPorts</span>
          <span class="breadcrumb-sep">/</span>
          <span class="breadcrumb-current">{{ pageTitle }}</span>
        </div>
        <div class="top-nav-right">
          <router-link v-if="isStudent" :to="{ name: 'create-report' }" class="btn btn-primary">
            <Plus :size="16" />
            <span class="btn-text">New Report</span>
          </router-link>
          <router-link v-if="isAdmin" :to="{ name: 'admin-create-user' }" class="btn btn-primary">
            <UserPlus :size="16" />
            <span class="btn-text">Add User</span>
          </router-link>
        </div>
      </header>

      <!-- Page description strip -->
      <div v-if="pageDescription !== ''" class="page-desc-strip">
        <p>{{ pageDescription }}</p>
      </div>

      <main class="page-content">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { Menu, Plus, UserPlus } from 'lucide-vue-next';
import SidebarComponent from './Sidebar.vue';

const authStore = useAuthStore();
const route = useRoute();

const sidebarOpen = ref(false);

const isAdmin = computed(() => authStore.isAdmin);
const isBDE = computed(() => authStore.isBDE);
const isTeacher = computed(() => authStore.isTeacher);
const isStudent = computed(() => authStore.isStudent);

const pageTitle = computed(() => {
  if (route.name === 'admin-dashboard') return 'Admin Control Panel';
  if (route.name === 'admin-create-user') return 'Add New User';
  if (route.name === 'admin-categories') return 'Categories';
  if (route.name === 'bde-dashboard') return 'BDE Dashboard';
  if (route.name === 'bde-meetings') return 'My Meetings';
  if (route.name === 'teacher-dashboard') return 'Teacher Dashboard';
  if (route.name === 'student-dashboard') return 'My Reports';
  if (route.name === 'create-report') return 'Create Report';
  return 'Dashboard';
});

const pageDescription = computed(() => {
  if (route.name === 'admin-dashboard') return 'Manage platform users and coordinate resolving meetings.';
  if (route.name === 'bde-dashboard') return 'Review and act on Infrastructure, Services, and Food reports.';
  if (route.name === 'teacher-dashboard') return 'Review and manage Formation, HR, and Pedagogy reports.';
  if (route.name === 'student-dashboard') return 'Manage and track your submitted problem reports.';
  return '';
});

const toggleSidebar = () => { sidebarOpen.value = !sidebarOpen.value; };
const closeSidebar = () => { sidebarOpen.value = false; };
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: var(--background);
}

.mobile-toggle {
  display: none;
  position: fixed;
  top: 0.875rem;
  left: 0.875rem;
  z-index: 1000;
  width: 40px;
  height: 40px;
  background: var(--surface-elevated);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  color: var(--text-main);
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.mobile-toggle:hover {
  background: var(--surface-hover);
  border-color: var(--glass-border-hover);
}

.sidebar-overlay { display: none; }

.sidebar {
  width: var(--sidebar-width, 280px);
  height: 100vh;
  position: sticky;
  top: 0;
  flex-shrink: 0;
}

.main-wrapper {
  flex: 1;
  min-width: 0;
  overflow-y: auto;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── Top nav bar ─────────────────────────────── */
.top-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.875rem 1.75rem;
  border-bottom: 1px solid var(--glass-border);
  background: rgba(17, 24, 39, 0.5);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  position: sticky;
  top: 0;
  z-index: 50;
  flex-shrink: 0;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.breadcrumb-root {
  color: var(--text-muted);
  font-weight: 500;
}

.breadcrumb-sep { color: var(--text-muted); opacity: 0.5; }

.breadcrumb-current {
  color: var(--text-main);
  font-weight: 600;
}

.top-nav-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.8125rem;
  text-decoration: none;
  transition: all var(--transition-fast);
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  color: white;
  box-shadow: 0 4px 12px var(--primary-glow);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px var(--primary-glow);
}

/* ── Page description strip ──────────────────── */
.page-desc-strip {
  padding: 0.625rem 1.75rem;
  border-bottom: 1px solid var(--glass-border);
  background: rgba(10, 14, 23, 0.3);
  flex-shrink: 0;
}

.page-desc-strip p {
  font-size: 0.8125rem;
  color: var(--text-muted);
  margin: 0;
}

/* ── Content ─────────────────────────────────── */
.page-content {
  flex: 1;
  padding: 1.75rem;
  overflow-y: visible;
}

/* ── Mobile / tablet ─────────────────────────── */
@media (max-width: 1024px) {
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    z-index: 999;
    transform: translateX(-100%);
    transition: transform var(--transition-normal);
  }

  .sidebar.open { transform: translateX(0); }

  .mobile-toggle { display: flex; }

  .sidebar-overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 998;
    opacity: 0;
    visibility: hidden;
    transition: all var(--transition-normal);
  }

  .sidebar-overlay.active { opacity: 1; visibility: visible; }

  .main-wrapper { padding-top: 3.5rem; }

  .top-nav { padding-left: 4rem; }
}

@media (max-width: 640px) {
  .page-content { padding: 1rem; }
  .top-nav { padding: 0.75rem 1rem 0.75rem 4rem; }
  .page-desc-strip { padding: 0.5rem 1rem; }
  .btn-text { display: none; }
  .btn { padding: 0.5rem; }
}
</style>