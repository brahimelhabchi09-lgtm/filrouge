import { defineStore } from 'pinia';
import api from '../services/api';

export const useReportsStore = defineStore('reports', {
  state: () => ({
    reports: [],
    generatedReports: [],
    categories: [],
    loading: false,
    error: null,
    pagination: { currentPage: 1, perPage: 10, total: 0 },
  }),
  actions: {
    init() {
      this.fetchMyReports();
      this.fetchCategories();
    },
    async fetchMyReports(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/student/my-reports?page=${page}`);
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

    async fetchGeneratedReports(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/v1/generated-reports?page=${page}`);
        this.generatedReports = response.data.data || [];
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchCategories() {
      try {
        const response = await api.get('/student/categories');
        this.categories = response.data.categories || [];
        return this.categories;
      } catch (error) {
        this.error = error.message;
        throw error;
      }
    },

    async createReport(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/student/create-report', data);
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
