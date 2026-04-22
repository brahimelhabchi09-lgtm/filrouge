import { defineStore } from 'pinia';
import api from '../services/api';

export const useGeneratedReportsStore = defineStore('generatedReports', {
  state: () => ({
    generatedReports: [],
    loading: false,
    error: '',
    createLoading: false,
    createError: '',
  }),
  actions: {
    async fetchGeneratedReports() {
      this.loading = true;
      this.error = '';

      try {
        const response = await api.get('/v1/generated-reports');
        this.generatedReports = response.data?.data || [];
      } catch (err) {
        this.error =
          err?.response?.data?.message || 'Failed to load generated reports.';
      } finally {
        this.loading = false;
      }
    },

    async createGeneratedReport(reportIds) {
      this.createLoading = true;
      this.createError = '';

      try {
        const payload = { report_ids: reportIds };
        const response = await api.post('/v1/generated-reports', payload);

        // Refresh list to keep state consistent with server.
        await this.fetchGeneratedReports();
        return response.data;
      } catch (err) {
        this.createError =
          err?.response?.data?.message || 'Failed to create generated report.';
        throw err;
      } finally {
        this.createLoading = false;
      }
    },
  },
});

