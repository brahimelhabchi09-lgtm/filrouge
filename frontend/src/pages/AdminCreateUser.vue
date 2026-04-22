<template>
  <div class="admin-create-user-container">
    <header class="header">
      <h2>Create New User</h2>
      <p class="subtitle">Admin dashboard user management</p>
    </header>

    <!-- Error/Success Alerts -->
    <div v-if="store.error" class="alert error">
      {{ store.error }}
    </div>
    <div v-if="store.successMessage" class="alert success">
      {{ store.successMessage }}
    </div>

    <!-- User Creation Form -->
    <form class="form" @submit.prevent="handleSubmit">
      <div class="field">
        <label for="name">Full Name</label>
        <input 
          id="name"
          v-model="form.name" 
          type="text" 
          required
          placeholder="e.g. John Doe"
        />
      </div>

      <div class="field">
        <label for="email">Email Address</label>
        <input 
          id="email"
          v-model="form.email" 
          type="email" 
          required
          placeholder="john@example.com"
        />
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input 
          id="password"
          v-model="form.password" 
          type="password" 
          required
          placeholder="••••••••"
          minlength="6"
        />
      </div>

      <div class="field">
        <label for="role">Role</label>
        <select 
          id="role"
          v-model="form.role" 
          required
        >
          <option value="" disabled>Select a role...</option>
          <option value="student">Student</option>
          <option value="teacher">Teacher</option>
          <option value="bde">BDE</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <div class="actions">
        <button 
          type="button" 
          class="btn-secondary"
          @click="resetForm"
        >
          Reset
        </button>
        <button 
          type="submit" 
          class="btn-primary"
          :disabled="store.loading"
        >
          {{ store.loading ? 'Creating...' : 'Create User' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAdminUsersStore } from '../stores/adminUsers';

// State Management
const store = useAdminUsersStore();

const initialFormState = {
  name: '',
  email: '',
  password: '',
  role: ''
};

const form = ref({ ...initialFormState });

// Methods
const handleSubmit = async () => {
  try {
    await store.createUser(form.value);
    // Only reset form state on success, store state (success message) persists
    form.value = { ...initialFormState };
  } catch (error) {
    // Errors are already handled inside the pinia store state
  }
};

const resetForm = () => {
  form.value = { ...initialFormState };
  store.clearMessages();
};

// Lifecycle Hooks (cleanup)
onMounted(() => {
  store.clearMessages();
});

onUnmounted(() => {
  store.clearMessages();
});
</script>

<style scoped>
.admin-create-user-container {
  max-width: 600px;
  margin: 40px auto;
  padding: 24px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  font-family: sans-serif;
}

.header {
  margin-bottom: 24px;
}

.header h2 {
  margin: 0 0 8px;
  color: #1f2937;
  font-size: 1.5rem;
}

.subtitle {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

/* Alerts */
.alert {
  padding: 12px 16px;
  margin-bottom: 20px;
  border-radius: 6px;
  font-size: 0.875rem;
}

.alert.error {
  background-color: #fee2e2;
  color: #b91c1c;
  border: 1px solid #f87171;
}

.alert.success {
  background-color: #dcfce3;
  color: #15803d;
  border: 1px solid #4ade80;
}

/* Form Styles */
.form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.field label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.field input,
.field select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
  color: #1f2937;
  background-color: #fff;
  transition: border-color 0.2s;
}

.field input:focus,
.field select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Actions */
.actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

button {
  padding: 10px 16px;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary {
  background-color: #fff;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-secondary:hover {
  background-color: #f9fafb;
}

.btn-primary {
  background-color: #2563eb;
  border: 1px solid #2563eb;
  color: #ffffff;
}

.btn-primary:hover {
  background-color: #1d4ed8;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #93c5fd;
  border-color: #93c5fd;
}
</style>
