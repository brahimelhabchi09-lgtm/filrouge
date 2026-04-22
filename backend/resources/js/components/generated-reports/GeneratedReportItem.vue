<template>
  <article class="card">
    <header class="card-header">
      <h3>Generated Report #{{ generatedReport.id }}</h3>
      <span class="muted">{{ formatDate(generatedReport.created_at) }}</span>
    </header>

    <div v-if="!generatedReport.reports || generatedReport.reports.length === 0" class="muted">
      No attached reports.
    </div>

    <ul v-else class="report-list">
      <li v-for="report in generatedReport.reports" :key="report.id" class="report-item">
        <div><strong>ID:</strong> {{ report.id }}</div>
        <div><strong>Body:</strong> {{ report.body || report.description || '-' }}</div>
        <div><strong>Category:</strong> {{ report.category?.name || '-' }}</div>
        <div><strong>Created:</strong> {{ formatDate(report.created_at) }}</div>
      </li>
    </ul>
  </article>
</template>

<script setup>
const props = defineProps({
  generatedReport: {
    type: Object,
    required: true,
  },
});

function formatDate(value) {
  if (!value) return '-';
  return new Date(value).toLocaleString();
}
</script>

<style scoped>
.card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 12px;
  background: #fff;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.report-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
}

.report-item {
  border: 1px solid #f1f5f9;
  border-radius: 6px;
  padding: 10px;
  background: #f8fafc;
}

.muted {
  color: #64748b;
}
</style>

