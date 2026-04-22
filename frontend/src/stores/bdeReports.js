import { defineStore } from 'pinia';
import api from '../services/api';

export const useBDEReportsStore = defineStore('bdeReports', {
  state: () => ({
    reports: [],
    requestMeetings: [],
    meetings: [],
    loading: false,
    error: null,
    pagination: { currentPage: 1, perPage: 10, total: 0 },
  }),
  actions: {
    init() {
      this.fetchDashboard();
    },
    async fetchDashboard(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/bde/dashboard?page=${page}`);
        this.reports = response.data.pendingReports || [];
        this.requestMeetings = response.data.requestMeetings || [];
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
        const response = await api.get(`/bde/reports?page=${page}`);
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

    async fetchRequestMeetings() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/request-meetings');
        this.requestMeetings = response.data.data || [];
        
        this.meetings = this.requestMeetings
          .filter(rm => rm.status === 'approved' && rm.meeting)
          .map(rm => ({
            ...rm.meeting,
            pdf_path: rm.generated_report?.pdf_path
          }));
        
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async approveReport(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post(`/bde/approve/${id}`, data);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async denyReport(id, reason) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post(`/bde/deny/${id}`, { reason });
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
