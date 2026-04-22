<template>
    <div class="form-container">
      <div class="header-back">
        <router-link :to="{ name: 'admin-dashboard' }" class="back-link">
          <ArrowLeft :size="16" />
          <span>Back to Control Panel</span>
        </router-link>
      </div>
      
      <div class="form-header">
        <h1>Add New User</h1>
        <p>Create a new account and assign a role to a student or staff member.</p>
      </div>

      <div v-if="errors.general" class="alert alert-error">
        <AlertCircle :size="16" />
        {{ errors.general }}
      </div>
      
      <form @submit.prevent="handleSubmit" class="glass-card form-card">
        <div class="form-grid">
          <div class="form-group">
            <label>First Name</label>
            <input 
              v-model="form.first_name"
              type="text" 
              class="form-control" 
              placeholder="e.g. John" 
              required
            />
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input 
              v-model="form.last_name"
              type="text" 
              class="form-control" 
              placeholder="e.g. Doe" 
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <div class="input-wrapper">
            <Mail :size="16" class="input-icon" />
            <input 
              v-model="form.email"
              type="email" 
              class="form-control with-icon" 
              placeholder="j.doe@school.com" 
              required
            />
          </div>
          <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
        </div>

        <div class="form-group">
          <label>Assigned Role</label>
          <div class="input-wrapper">
            <Shield :size="16" class="input-icon" />
            <select v-model="form.role" class="form-control with-icon custom-select" required>
              <option value="" disabled>Select a role...</option>
              <option value="student">Student</option>
              <option value="teacher">Teacher</option>
              <option value="bde">BDE Member</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
          <span v-if="errors.role" class="error-message">{{ errors.role }}</span>
        </div>

        <div class="form-group">
          <label>Temporary Password</label>
          <div class="input-wrapper">
            <Lock :size="16" class="input-icon" />
            <input 
              v-model="form.password"
              type="password" 
              class="form-control with-icon" 
              required
            />
          </div>
          <p class="password-hint">User will be prompted to change this on first login.</p>
        </div>

        <div class="form-actions">
          <router-link :to="{ name: 'admin-dashboard' }" class="btn btn-ghost">Cancel</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading" class="spinner-sm"></span>
            <template v-else>
              <UserPlus :size="15" />
              <span>Create Account</span>
            </template>
          </button>
        </div>
      </form>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAdminStore } from '../../stores/admin';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import { ArrowLeft, UserPlus, AlertCircle, Mail, Shield, Lock } from 'lucide-vue-next';

const router = useRouter();
const adminStore = useAdminStore();

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  role: '',
  password: 'WelcomeYOUPORTS2026'
});

const errors = reactive({
  email: '',
  role: '',
  general: ''
});

const loading = ref(false);

const handleSubmit = async () => {
  errors.email = '';
  errors.role = '';
  errors.general = '';

  if (!form.first_name || !form.last_name) {
    errors.general = 'First name and last name are required';
    return;
  }

  loading.value = true;

  try {
    await adminStore.createUser(form);
    router.push({ name: 'admin-dashboard' });
  } catch (error) {
    console.error('Error creating user:', error);
    if (error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      if (validationErrors.email) errors.email = validationErrors.email[0];
      if (validationErrors.role) errors.role = validationErrors.role[0];
      if (validationErrors.first_name) errors.general = validationErrors.first_name[0];
      if (validationErrors.last_name) errors.general = validationErrors.last_name[0];
    } else {
      errors.general = error.response?.data?.message || 'Failed to create user';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.form-container {
  max-width: 600px;
  margin: 0 auto;
  animation: slide-up 0.3s ease both;
}

.header-back {
  margin-bottom: 1.5rem;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  text-decoration: none;
  font-size: 0.8125rem;
  font-weight: 500;
  transition: color var(--transition-fast);
}

.back-link:hover {
  color: var(--text-main);
}

.form-header {
  margin-bottom: 2rem;
}

.form-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-main);
  margin: 0 0 0.5rem 0;
}

.form-header p {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin: 0;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1rem;
  border-radius: var(--radius-md);
  margin-bottom: 1.5rem;
  font-size: 0.8125rem;
  font-weight: 500;
}

.alert-error {
  background: var(--error-bg);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: var(--error);
}

.form-card {
  padding: 2.25rem;
  border-top: 3px solid;
  border-image: linear-gradient(90deg, var(--primary), var(--accent)) 1;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-main);
  font-weight: 600;
  font-size: 0.8125rem;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  pointer-events: none;
}

.form-control {
  width: 100%;
  background: var(--background);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  color: var(--text-main);
  font-family: inherit;
  font-size: 0.875rem;
  transition: all var(--transition-fast);
}

.form-control.with-icon {
  padding-left: 2.5rem;
}

.form-control::placeholder {
  color: var(--text-muted);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: var(--ring-glow);
}

.custom-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
}

.error-message {
  color: var(--error);
  font-size: 0.75rem;
  display: block;
  margin-top: 0.375rem;
}

.password-hint {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin: 0.5rem 0 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--glass-border);
}

/* Base button styles matching DashboardLayout */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1.25rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.875rem;
  text-decoration: none;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: all var(--transition-fast);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  color: white;
  box-shadow: 0 4px 12px var(--primary-glow);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px var(--primary-glow);
}

.btn-ghost {
  background: transparent;
  color: var(--text-muted);
  border: 1px solid transparent;
}

.btn-ghost:hover {
  color: var(--text-main);
  background: var(--surface-hover);
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  .form-card {
    padding: 1.5rem;
  }
}
</style>
