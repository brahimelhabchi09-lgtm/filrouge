import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || '',
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),
    isAdmin: (state) => state.user?.role?.toUpperCase() === 'ADMIN',
    isBDE: (state) => state.user?.role?.toUpperCase() === 'BDE',
    isTeacher: (state) => state.user?.role?.toUpperCase() === 'TEACHER',
    isStudent: (state) => state.user?.role?.toUpperCase() === 'STUDENT',
    userRole: (state) => state.user?.role?.toUpperCase() || '',
  },
  actions: {
    init() {
      this.token = localStorage.getItem('auth_token') || '';
      this.user = JSON.parse(localStorage.getItem('auth_user') || 'null');
    },
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
    async login(email, password) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          credentials: 'include',
          body: JSON.stringify({ email, password }),
        });

        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
          data = await response.json();
        } else {
          const text = await response.text();
          console.error('Non-JSON response:', text);
          throw new Error('Invalid server response');
        }

        if (!response.ok) {
          throw new Error(data.message || `Login failed with status ${response.status}`);
        }

        this.setAuth({
          token: 'session_' + Date.now(),
          user: data.user,
        });

        return data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async logout() {
      try {
        await fetch('/api/logout', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
          },
          credentials: 'include',
        });
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.clearAuth();
      }
    },
    async fetchUser() {
      if (!this.token) return null;
      
      try {
        const response = await api.get('/student/dashboard');
        return response.data;
      } catch (error) {
        console.error('Fetch user error:', error);
        return null;
      }
    },
  },
});
