import { defineStore } from 'pinia';
import api from '../services/api';

export const useAdminStore = defineStore('admin', {
  state: () => ({
    users: [],
    meetings: [],
    requestMeetings: [],
    categories: [],
    loading: false,
    error: null,
  }),
  actions: {
    init() {
      this.fetchCategories();
    },
    async fetchUsers(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/admin/dashboard?page=${page}`);
        this.users = response.data.users || [];
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
        const response = await api.get(`/admin/reports?page=${page}`);
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchMeetings(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/admin/meetings?page=${page}`);
        this.meetings = response.data.data || [];
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchRequestMeetings(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/admin/request-meetings?page=${page}`);
        this.requestMeetings = response.data.data || [];
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchCategories() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/admin/categories');
        this.categories = response.data.categories || [];
        return this.categories;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createCategory(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/admin/categories', data);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateCategory(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.put(`/admin/categories/${id}`, data);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteCategory(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.delete(`/admin/categories/${id}`);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createMeeting(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/meetings', data);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async rejectRequestMeeting(id, reason) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post(`/meetings/reject/${id}`, { reason });
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createUser(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/admin/create-user', data);
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
