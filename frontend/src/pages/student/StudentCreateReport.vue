<template>
    <div class="form-container">
      <div class="header-back">
        <router-link :to="{ name: 'dashboard' }" class="back-link">
          <ArrowLeft :size="16" />
          <span>Back to Dashboard</span>
        </router-link>
      </div>

      <div class="form-header">
        <h1>Create New Report</h1>
        <p>Your report will be automatically analyzed by AI for classification and priority mapping.</p>
      </div>

      <div v-if="errors.general" class="alert alert-error">
        <AlertCircle :size="16" />
        {{ errors.general }}
      </div>

      <form @submit.prevent="handleSubmit" class="glass-card form-card">
        <div class="form-grid">
          <div class="form-group span-full">
            <label>Report Title</label>
            <div class="input-wrapper">
              <Type :size="16" class="input-icon" />
              <input 
                v-model="form.title"
                type="text" 
                class="form-control with-icon" 
                placeholder="e.g. Projector not working in Room 101" 
                required
              />
            </div>
            <span v-if="errors.title" class="error-message">{{ errors.title }}</span>
          </div>

          <div class="form-group span-full">
            <label>Category</label>
            <div class="input-wrapper">
              <Tag :size="16" class="input-icon" />
              <select v-model="form.category_id" class="form-control with-icon custom-select" required>
                <option value="" disabled>Select category...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
            <span v-if="errors.category_id" class="error-message">{{ errors.category_id }}</span>
          </div>

          <div class="form-group span-full">
            <label>Detailed Description</label>
            <textarea 
              v-model="form.description"
              class="form-control textarea" 
              placeholder="Describe the problem in detail. The more info you provide, the better the AI can support you."
              required
            ></textarea>
            <span v-if="errors.description" class="error-message">{{ errors.description }}</span>
          </div>
        </div>

        <div class="form-actions">
          <router-link :to="{ name: 'dashboard' }" class="btn btn-ghost">Cancel</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading" class="spinner-sm"></span>
            <template v-else>
              <span>Submit Report</span>
              <Send :size="15" />
            </template>
          </button>
        </div>
      </form>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useReportsStore } from '../../stores/reports';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import { ArrowLeft, Send, AlertCircle, Type, Tag } from 'lucide-vue-next';

const router = useRouter();
const reportsStore = useReportsStore();

const form = reactive({
  title: '',
  category_id: '',
  description: ''
});

const errors = reactive({
  title: '',
  category_id: '',
  description: '',
  general: ''
});

const categories = ref([]);
const loading = ref(false);

onMounted(async () => {
  try {
    await reportsStore.fetchCategories();
    categories.value = reportsStore.categories;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
});

const handleSubmit = async () => {
  errors.title = '';
  errors.category_id = '';
  errors.description = '';
  errors.general = '';

  if (form.title.length < 5) {
    errors.title = 'Title must be at least 5 characters.';
    return;
  }

  if (form.description.length < 10) {
    errors.description = 'Description must be at least 10 characters.';
    return;
  }

  loading.value = true;

  try {
    await reportsStore.createReport({
      title: form.title,
      category_id: form.category_id,
      description: form.description,
    });
    router.push({ name: 'dashboard' });
  } catch (error) {
    console.error('Error creating report:', error);
    errors.general = error.message || 'Failed to create report.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.form-container {
  max-width: 640px;
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
  grid-template-columns: 1fr;
  gap: 1.25rem;
}

.span-full {
  grid-column: 1 / -1;
}

.form-group {
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

.textarea {
  min-height: 140px;
  resize: vertical;
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

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--glass-border);
}

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
  .form-card {
    padding: 1.5rem;
  }
}
</style>
