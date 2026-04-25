<template>
  <div class="app">
    <animated-background />
    <RouterView />
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import AnimatedBackground from './components/AnimatedBackground.vue';
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

:root {
  /* Pure Black Base */
  --background: #000000;
  --surface: #0a0a0a;
  --surface-elevated: #111111;
  --surface-hover: #1a1a1a;
  --surface-card: rgba(10, 10, 10, 0.95);

  /* Neon Accent Palette */
  --primary: #7c3aed;
  --primary-hover: #6d28d9;
  --primary-light: #a78bfa;
  --primary-glow: rgba(124, 58, 237, 0.5);

  --secondary: #ec4899;
  --secondary-hover: #db2777;
  --secondary-glow: rgba(236, 72, 153, 0.4);

  --accent: #06b6d4;
  --accent-hover: #0891b2;
  --accent-glow: rgba(6, 182, 212, 0.4);

  --neon-green: #10b981;
  --neon-green-glow: rgba(16, 185, 129, 0.4);

  /* Text */
  --text-main: #ffffff;
  --text-secondary: #e2e8f0;
  --text-muted: #4b5563;

  /* Priority */
  --p0: #f43f5e;
  --p0-bg: rgba(244, 63, 94, 0.1);
  --p1: #f59e0b;
  --p1-bg: rgba(245, 158, 11, 0.1);
  --p2: #06b6d4;
  --p2-bg: rgba(6, 182, 212, 0.1);
  --p3: #7c3aed;
  --p3-bg: rgba(124, 58, 237, 0.1);

  /* Role Colors */
  --role-admin: #a78bfa;
  --role-admin-bg: rgba(167, 139, 250, 0.1);
  --role-bde: #06b6d4;
  --role-bde-bg: rgba(6, 182, 212, 0.1);
  --role-teacher: #10b981;
  --role-teacher-bg: rgba(16, 185, 129, 0.1);
  --role-student: #f59e0b;
  --role-student-bg: rgba(245, 158, 11, 0.1);

  /* Status */
  --success: #10b981;
  --success-bg: rgba(16, 185, 129, 0.1);
  --warning: #f59e0b;
  --warning-bg: rgba(245, 158, 11, 0.1);
  --error: #f43f5e;
  --error-bg: rgba(244, 63, 94, 0.1);
  --pending: #f59e0b;
  --pending-bg: rgba(245, 158, 11, 0.1);
  --refused: #f43f5e;
  --refused-bg: rgba(244, 63, 94, 0.1);

  /* Glass */
  --glass: rgba(255, 255, 255, 0.02);
  --glass-border: rgba(255, 255, 255, 0.06);
  --glass-border-hover: rgba(124, 58, 237, 0.4);
  --ring-glow: 0 0 0 2px var(--primary-glow);

  /* Shadows */
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.8);
  --shadow-md: 0 4px 12px rgba(0,0,0,0.8);
  --shadow-lg: 0 16px 40px rgba(0,0,0,0.9);
  --shadow-card: 0 4px 24px rgba(0,0,0,0.6);
  --shadow-glow: 0 0 40px var(--primary-glow);

  /* Layout */
  --sidebar-width: 260px;

  /* Radius */
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 14px;
  --radius-xl: 20px;
  --radius-full: 9999px;

  /* Transitions */
  --transition-fast: 120ms cubic-bezier(0.4,0,0.2,1);
  --transition-normal: 220ms cubic-bezier(0.4,0,0.2,1);
  --transition-slow: 380ms cubic-bezier(0.4,0,0.2,1);
}

*, *::before, *::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

html {
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

body {
  background-color: var(--background);
  color: var(--text-main);
  min-height: 100vh;
  overflow-x: hidden;
  line-height: 1.6;
}

a { color: inherit; text-decoration: none; }
button { font-family: inherit; cursor: pointer; border: none; background: none; }
input, textarea, select { font-family: inherit; font-size: inherit; }

.app {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

@media (max-width: 640px) { html { font-size: 14px; } }

/* ── Page Transitions ─────────────────────── */
.page-enter-active { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
.page-leave-active { transition: all 0.2s cubic-bezier(0.4,0,0.2,1); }
.page-enter-from { opacity: 0; transform: translateY(12px) scale(0.99); }
.page-leave-to  { opacity: 0; transform: translateY(-8px) scale(0.99); }

/* ── Scrollbar ────────────────────────────── */
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, var(--primary) 0%, var(--accent) 100%);
  border-radius: var(--radius-full);
}
::-webkit-scrollbar-thumb:hover { background: var(--primary-light); }

::selection { background: var(--primary); color: white; }
:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

/* ── Keyframes ────────────────────────────── */
@keyframes pulse-glow {
  0%, 100% { box-shadow: 0 0 20px var(--primary-glow); }
  50% { box-shadow: 0 0 50px var(--primary-glow), 0 0 80px var(--primary-glow); }
}

@keyframes float {
  0%, 100% { transform: translate(0,0) scale(1); }
  25%  { transform: translate(20px,-25px) scale(1.04); }
  50%  { transform: translate(-15px,15px) scale(0.96); }
  75%  { transform: translate(-25px,-15px) scale(1.02); }
}

@keyframes slide-up {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

@keyframes slide-in-left {
  from { opacity: 0; transform: translateX(-16px); }
  to   { opacity: 1; transform: translateX(0); }
}

@keyframes fade-in {
  from { opacity: 0; }
  to   { opacity: 1; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes shimmer {
  0%   { background-position: -200% 0; }
  100% { background-position:  200% 0; }
}

@keyframes gradient-shift {
  0%, 100% { background-position: 0% 50%; }
  50%       { background-position: 100% 50%; }
}

@keyframes neon-pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.6; }
}

@keyframes bar-in {
  from { transform: translateY(-50%) scaleY(0); }
  to   { transform: translateY(-50%) scaleY(1); }
}

@keyframes count-up {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Glass Card ───────────────────────────── */
.glass-card {
  background: var(--surface-card);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  transition: border-color var(--transition-normal), box-shadow var(--transition-normal), transform var(--transition-normal);
  box-shadow: var(--shadow-card), inset 0 1px 0 rgba(255,255,255,0.04);
}

.glass-card:hover {
  border-color: var(--glass-border-hover);
  box-shadow: var(--shadow-lg), 0 0 0 1px rgba(124,58,237,0.15), inset 0 1px 0 rgba(255,255,255,0.06);
}

/* ── Stat Card ────────────────────────────── */
.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--surface-card);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-card);
  transition: all var(--transition-normal);
  animation: slide-up 0.5s cubic-bezier(0.4,0,0.2,1) both;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(124,58,237,0.04) 0%, transparent 60%);
  pointer-events: none;
}

.stat-card:hover {
  border-color: rgba(124,58,237,0.3);
  transform: translateY(-3px);
  box-shadow: var(--shadow-lg), 0 0 20px rgba(124,58,237,0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
}

.stat-label {
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--text-muted);
  font-weight: 600;
  margin: 0 0 0.25rem;
}

.stat-value {
  font-size: 1.875rem;
  font-weight: 800;
  margin: 0;
  color: var(--text-main);
  line-height: 1;
  letter-spacing: -0.03em;
  animation: count-up 0.5s ease both;
}

/* ── Section ──────────────────────────────── */
.section-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-main);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  letter-spacing: -0.01em;
}

.section-count {
  font-size: 0.6875rem;
  font-weight: 700;
  background: rgba(124,58,237,0.15);
  color: var(--primary-light);
  padding: 0.15rem 0.6rem;
  border-radius: var(--radius-full);
  border: 1px solid rgba(124,58,237,0.2);
}

/* ── Empty State ──────────────────────────── */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  animation: fade-in 0.4s ease;
}
.empty-state svg { opacity: 0.2; }
.empty-state p { font-size: 0.9375rem; margin: 0; }

/* ── Spinner ──────────────────────────────── */
.spinner {
  width: 32px;
  height: 32px;
  border: 2px solid rgba(124,58,237,0.15);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  margin: 0 auto;
  box-shadow: 0 0 12px var(--primary-glow);
}

.spinner-sm {
  width: 14px;
  height: 14px;
  border-width: 2px;
  margin: 0;
}

/* ── Animate-in ───────────────────────────── */
.animate-in { animation: slide-up 0.4s cubic-bezier(0.4,0,0.2,1) both; }

/* ── Form Controls ────────────────────────── */
.form-control {
  width: 100%;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  color: var(--text-main);
  transition: all var(--transition-fast);
}
.form-control::placeholder { color: var(--text-muted); }
.form-control:focus {
  outline: none;
  border-color: var(--primary);
  background: rgba(124,58,237,0.05);
  box-shadow: 0 0 0 3px rgba(124,58,237,0.15), 0 0 20px rgba(124,58,237,0.1);
}

/* ── Buttons ──────────────────────────────── */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.875rem;
  transition: all var(--transition-fast);
  white-space: nowrap;
  position: relative;
  overflow: hidden;
}

.btn::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 50%);
  opacity: 0;
  transition: opacity var(--transition-fast);
}
.btn:hover::after { opacity: 1; }

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, #5b21b6 100%);
  color: white;
  box-shadow: 0 4px 15px var(--primary-glow), inset 0 1px 0 rgba(255,255,255,0.1);
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px var(--primary-glow), inset 0 1px 0 rgba(255,255,255,0.15);
}
.btn-primary:active { transform: translateY(0); }

.btn-secondary {
  background: var(--surface-elevated);
  color: var(--text-main);
  border: 1px solid var(--glass-border);
}
.btn-secondary:hover {
  background: var(--surface-hover);
  border-color: rgba(124,58,237,0.3);
}

.btn-ghost {
  color: var(--text-muted);
  border: 1px solid var(--glass-border);
}
.btn-ghost:hover {
  color: var(--text-main);
  background: rgba(255,255,255,0.04);
  border-color: rgba(255,255,255,0.12);
}

.btn-success {
  background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(16,185,129,0.3);
}
.btn-success:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }

.btn-danger {
  background: linear-gradient(135deg, var(--error) 0%, #e11d48 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(244,63,94,0.3);
}
.btn-danger:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(244,63,94,0.4); }

/* ── Badges ───────────────────────────────── */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.625rem;
  border-radius: var(--radius-full);
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.badge-success { background: var(--success-bg); color: var(--success); border: 1px solid rgba(16,185,129,0.2); }
.badge-warning { background: var(--warning-bg); color: var(--warning); border: 1px solid rgba(245,158,11,0.2); }
.badge-error   { background: var(--error-bg);   color: var(--error);   border: 1px solid rgba(244,63,94,0.2); }

/* ── Number input ─────────────────────────── */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
input[type="number"] { -moz-appearance: textfield; }
</style>