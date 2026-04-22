import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || '',
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    isAdmin: (state) =>
      String(state.user?.role || '').toUpperCase() === 'ADMIN',
  },
  actions: {
    setAuth({ token, user }) {
      this.token = token || '';
      this.user = user || null;
      localStorage.setItem('auth_token', this.token);
      localStorage.setItem('auth_user', JSON.stringify(this.user));
    },
    clearAuth() {
      this.token = '';
      this.user = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
    },
  },
});

