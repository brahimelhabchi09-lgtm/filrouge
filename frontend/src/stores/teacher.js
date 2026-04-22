import { defineStore } from 'pinia';
import api from '../services/api';

export const useTeacherStore = defineStore('teacher', {
  state: () => ({
    reports: [],
    rejectedReasons: [],
    users: [],
    loading: false,
    error: null,
    pagination: { currentPage: 1, perPage: 10, total: 0 },
  }),
  actions: {
    init() {
      this.fetchDashboard();
    },
    async fetchDashboard() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/teacher/dashboard');
        this.reports = response.data.pendingReports || [];
        this.rejectedReasons = response.data.rejectedReasons || [];
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchReports(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/teacher/reports?page=${page}`);
        this.reports = response.data.data || [];
        this.pagination = {
          currentPage: response.data.meta?.current_page || 1,
          perPage: response.data.meta?.per_page || 10,
          total: response.data.meta?.total || 0,
        };
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchUsers() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/teacher/users');
        this.users = response.data.users || [];
        return this.users;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async resolveReport(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post(`/teacher/reports/${id}/resolve`);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async rejectReport(id, reason) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post(`/teacher/reports/${id}/reject`, { reason });
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
