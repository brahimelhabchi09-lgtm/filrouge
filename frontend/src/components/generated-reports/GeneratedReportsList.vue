<template>
  <section>
    <h2>Generated Reports</h2>

    <p v-if="loading" class="muted">Loading...</p>
    <p v-else-if="error" class="error">{{ error }}</p>
    <p v-else-if="generatedReports.length === 0" class="muted">No generated reports found.</p>

    <GeneratedReportItem
      v-for="item in generatedReports"
      :key="item.id"
      :generated-report="item"
    />
  </section>
</template>

<script setup>
import GeneratedReportItem from './GeneratedReportItem.vue';

defineProps({
  generatedReports: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
});
</script>

<style scoped>
.muted {
  color: #64748b;
}

.error {
  color: #dc2626;
}
</style>

