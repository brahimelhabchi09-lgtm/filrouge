<template>
  <GeneratedReportsListComponent
    :generated-reports="store.generatedReports"
    :loading="store.loading"
    :error="store.error"
  />
</template>

<script setup>
import { onMounted } from 'vue';
import { useGeneratedReportsStore } from '../stores/generatedReports';
import GeneratedReportsListComponent from '../components/generated-reports/GeneratedReportsList.vue';

const store = useGeneratedReportsStore();

onMounted(() => {
  store.fetchGeneratedReports();
});
</script>

