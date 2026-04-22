<template>
  <section>
    <h2>Create Generated Report (Admin)</h2>

    <p v-if="loadError" class="error">{{ loadError }}</p>

    <form class="form" @submit.prevent="onSubmit">
      <div class="field">
        <label>Select Reports</label>
        <p v-if="reportsLoading" class="muted">Loading reports...</p>
        <div v-else-if="availableReports.length === 0" class="muted">
          No reports available.
        </div>
        <div v-else class="checkbox-list">
          <label
            v-for="report in availableReports"
            :key="report.id"
            class="checkbox-item"
          >
            <input
              v-model="selectedReportIds"
              type="checkbox"
              :value="report.id"
            />
            <span>
              #{{ report.id }} - {{ report.body || report.description || 'No body' }}
              ({{ report.category?.name || '-' }})
            </span>
          </label>
        </div>
      </div>

      <p v-if="store.createError" class="error">{{ store.createError }}</p>
      <p v-if="successMessage" class="success">{{ successMessage }}</p>

      <button type="submit" :disabled="store.createLoading || selectedReportIds.length === 0">
        {{ store.createLoading ? 'Creating...' : 'Create Generated Report' }}
      </button>
    </form>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';
import { useGeneratedReportsStore } from '../stores/generatedReports';

const store = useGeneratedReportsStore();
const availableReports = ref([]);
const selectedReportIds = ref([]);
const reportsLoading = ref(false);
const loadError = ref('');
const successMessage = ref('');

async function loadReports() {
  reportsLoading.value = true;
  loadError.value = '';
  try {
    const response = await api.get('/v1/reports');
    availableReports.value = response.data?.data || [];
  } catch (err) {
    loadError.value =
      err?.response?.data?.message || 'Failed to load reports for selection.';
  } finally {
    reportsLoading.value = false;
  }
}

async function onSubmit() {
  successMessage.value = '';
  try {
    await store.createGeneratedReport(selectedReportIds.value);
    successMessage.value = 'Generated report created successfully.';
    selectedReportIds.value = [];
    await loadReports();
  } catch (err) {
    // Store exposes user-friendly error text.
  }
}

onMounted(() => {
  loadReports();
});
</script>

<style scoped>
.form {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  background: #fff;
}

.field {
  margin-bottom: 16px;
}

.checkbox-list {
  display: grid;
  gap: 8px;
}

.checkbox-item {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  border: 1px solid #f1f5f9;
  border-radius: 6px;
  padding: 8px;
}

.muted {
  color: #64748b;
}

.error {
  color: #dc2626;
}

.success {
  color: #16a34a;
}

button {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 14px;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>

