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
:root {
  /* Primary Colors */
  --primary: #6366f1;
  --primary-hover: #4f46e5;
  --primary-light: #818cf8;
  --primary-glow: rgba(99, 102, 241, 0.4);
  
  /* Secondary Colors */
  --secondary: #ec4899;
  --secondary-hover: #db2777;
  --accent: #8b5cf6;
  --accent-hover: #7c3aed;
  
  /* Background */
  --background: #0a0e17;
  --surface: #111827;
  --surface-elevated: #1a2234;
  --surface-hover: #243048;
  --surface-card: rgba(17, 24, 39, 0.85);
  
  /* Text */
  --text-main: #f1f5f9;
  --text-secondary: #cbd5e1;
  --text-muted: #64748b;
  
  /* Priority Colors */
  --p0: #ef4444;
  --p0-bg: rgba(239, 68, 68, 0.12);
  --p1: #f59e0b;
  --p1-bg: rgba(245, 158, 11, 0.12);
  --p2: #3b82f6;
  --p2-bg: rgba(59, 130, 246, 0.12);
  --p3: #6366f1;
  --p3-bg: rgba(99, 102, 241, 0.12);

  /* Role Colors */
  --role-admin: #6366f1;
  --role-admin-bg: rgba(99, 102, 241, 0.12);
  --role-bde: #8b5cf6;
  --role-bde-bg: rgba(139, 92, 246, 0.12);
  --role-teacher: #10b981;
  --role-teacher-bg: rgba(16, 185, 129, 0.12);
  --role-student: #38bdf8;
  --role-student-bg: rgba(56, 189, 248, 0.12);
  
  /* Status Colors */
  --success: #10b981;
  --success-bg: rgba(16, 185, 129, 0.15);
  --warning: #f59e0b;
  --warning-bg: rgba(245, 158, 11, 0.15);
  --error: #ef4444;
  --error-bg: rgba(239, 68, 68, 0.15);
  --pending: #f59e0b;
  --pending-bg: rgba(245, 158, 11, 0.15);
  --refused: #ef4444;
  --refused-bg: rgba(239, 68, 68, 0.15);
  
  /* Glass Effect */
  --glass: rgba(255, 255, 255, 0.03);
  --glass-border: rgba(255, 255, 255, 0.08);
  --glass-border-hover: rgba(255, 255, 255, 0.18);
  --ring-glow: 0 0 0 3px var(--primary-glow);
  
  /* Shadows */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
  --shadow-glow: 0 0 40px var(--primary-glow);
  --shadow-card: 0 4px 24px rgba(0, 0, 0, 0.35);
  
  /* Layout */
  --sidebar-width: 280px;
  
  /* Spacing */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  --radius-full: 9999px;
  
  /* Transitions */
  --transition-fast: 150ms ease;
  --transition-normal: 250ms ease;
  --transition-slow: 400ms ease;
}

*, *::before, *::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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

a {
  color: inherit;
  text-decoration: none;
}

button {
  font-family: inherit;
  cursor: pointer;
  border: none;
  background: none;
}

input, textarea, select {
  font-family: inherit;
  font-size: inherit;
}

.app {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

/* Responsive breakpoints */
@media (max-width: 640px) {
  html {
    font-size: 14px;
  }
}

/* Page Transitions */
.page-enter-active,
.page-leave-active {
  transition: all var(--transition-normal);
}

.page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Scrollbar Styling */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: var(--surface);
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, var(--primary) 0%, var(--accent) 100%);
  border-radius: var(--radius-full);
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, var(--primary-light) 0%, var(--primary) 100%);
}

/* Selection */
::selection {
  background: var(--primary);
  color: white;
}

/* Focus Styles */
:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

/* Utility Classes */
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}

/* Loading Animation */
@keyframes pulse-glow {
  0%, 100% { box-shadow: 0 0 20px var(--primary-glow); }
  50% { box-shadow: 0 0 40px var(--primary-glow), 0 0 60px var(--primary-glow); }
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

@keyframes slide-up {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes gradient-shift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Button Base Styles */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: var(--radius-md);
  font-weight: 500;
  font-size: 0.9375rem;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  box-shadow: 0 4px 15px var(--primary-glow);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px var(--primary-glow);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-secondary {
  background: var(--surface-elevated);
  color: var(--text-main);
  border: 1px solid var(--glass-border);
}

.btn-secondary:hover {
  background: var(--surface-hover);
  border-color: var(--glass-border-hover);
}

.btn-ghost {
  color: var(--text-secondary);
}

.btn-ghost:hover {
  color: var(--text-main);
  background: var(--glass);
}

.btn-success {
  background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
  color: white;
}

.btn-danger {
  background: linear-gradient(135deg, var(--error) 0%, #dc2626 100%);
  color: white;
}

/* Card Base */
.glass-card {
  background: var(--surface-card);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  transition: all var(--transition-normal);
  box-shadow: var(--shadow-card);
}

.glass-card:hover {
  border-color: var(--glass-border-hover);
  box-shadow: var(--shadow-lg), 0 0 0 1px rgba(99, 102, 241, 0.1);
}

/* Shared Stat Card */
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
  animation: slide-up 0.4s ease both;
}

.stat-card:hover {
  border-color: var(--glass-border-hover);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-label {
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  font-weight: 600;
  margin: 0 0 0.25rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0;
  color: var(--text-main);
  line-height: 1;
  letter-spacing: -0.02em;
}

/* Section Title */
.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-main);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-count {
  font-size: 0.75rem;
  font-weight: 600;
  background: var(--primary-glow);
  color: var(--primary-light);
  padding: 0.125rem 0.625rem;
  border-radius: var(--radius-full);
}

/* Shared Empty State */
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

.empty-state svg {
  opacity: 0.35;
}

.empty-state p {
  font-size: 0.9375rem;
  margin: 0;
}

/* Shared loading spinner */
.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid var(--glass-border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto;
}

.spinner-sm {
  width: 16px;
  height: 16px;
  border-width: 2px;
  margin: 0;
}

/* Animate-in helper */
.animate-in {
  animation: slide-up 0.4s ease both;
}

/* Form Elements */
.form-control {
  width: 100%;
  background: var(--surface);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  color: var(--text-main);
  transition: all var(--transition-fast);
}

.form-control::placeholder {
  color: var(--text-muted);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-glow);
}

/* Badge Base */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.badge-success {
  background: var(--success-bg);
  color: var(--success);
}

.badge-warning {
  background: var(--warning-bg);
  color: var(--warning);
}

.badge-error {
  background: var(--error-bg);
  color: var(--error);
}

/* Input Spinner */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>