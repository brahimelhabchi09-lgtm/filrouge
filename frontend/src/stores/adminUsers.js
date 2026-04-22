import { defineStore } from 'pinia';
import api from '../services/api';

export const useAdminUsersStore = defineStore('adminUsers', {
  state: () => ({
    loading: false,
    error: '',
    successMessage: '',
  }),
  actions: {
    async createUser(userData) {
      this.loading = true;
      this.error = '';
      this.successMessage = '';
      
      try {
        const response = await api.post('/admin/create-user', userData);
        this.successMessage = response.data?.message || 'User created successfully!';
        return response.data;
      } catch (err) {
        this.error = err?.response?.data?.message || 'Failed to create user. Please try again.';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    clearMessages() {
      this.error = '';
      this.successMessage = '';
    }
  }
});
