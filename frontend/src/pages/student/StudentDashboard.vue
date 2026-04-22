<template>
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card" style="--delay: 0s">
        <div class="stat-icon" style="background: var(--p3-bg); color: var(--p3)">
          <FileText :size="22" />
        </div>
        <div>
          <p class="stat-label">Total Reports</p>
          <p class="stat-value">{{ stats.total }}</p>
        </div>
      </div>
      <div class="stat-card" style="--delay: 0.07s">
        <div class="stat-icon" style="background: var(--p1-bg); color: var(--p1)">
          <Clock :size="22" />
        </div>
        <div>
          <p class="stat-label">Pending</p>
          <p class="stat-value">{{ stats.pending }}</p>
        </div>
      </div>
      <div class="stat-card" style="--delay: 0.14s">
        <div class="stat-icon" style="background: var(--success-bg); color: var(--success)">
          <CheckCircle :size="22" />
        </div>
        <div>
          <p class="stat-label">Resolved</p>
          <p class="stat-value">{{ stats.resolved }}</p>
        </div>
      </div>
      <div class="stat-card" style="--delay: 0.21s">
        <div class="stat-icon" style="background: var(--p0-bg); color: var(--p0)">
          <XCircle :size="22" />
        </div>
        <div>
          <p class="stat-label">Rejected</p>
          <p class="stat-value">{{ stats.rejected }}</p>
        </div>
      </div>
    </div>

    <!-- Filter toolbar -->
    <div class="filter-toolbar">
      <button
        v-for="f in filters"
        :key="f.value"
        class="filter-pill"
        :class="{ active: activeFilter === f.value }"
        @click="activeFilter = f.value"
      >
        {{ f.label }}
        <span class="filter-count">{{ filterCount(f.value) }}</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading reports…</p>
    </div>

    <!-- Empty -->
    <div v-else-if="filteredReports.length === 0" class="empty-state">
      <Inbox :size="48" />
      <p v-if="activeFilter === 'all'">No reports yet. Create your first report!</p>
      <p v-else>No {{ activeFilter }} reports.</p>
      <router-link v-if="activeFilter === 'all'" :to="{ name: 'create-report' }" class="btn btn-primary">
        <PlusCircle :size="16" /> Create Report
      </router-link>
    </div>

    <!-- Reports grid -->
    <div v-else class="reports-grid">
      <report-card
        v-for="report in filteredReports"
        :key="report.id"
        :title="report.title"
        :category="report.category?.name || 'Uncategorized'"
        :priority="report.generated_report?.priority || 'P2'"
        :status="formatStatus(report.status)"
        :description="report.description"
        :refusal-reason="report.refusal_reason"
      >
        <span class="report-date">{{ formatDate(report.created_at) }}</span>
      </report-card>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useReportsStore } from '../../stores/reports';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import ReportCard from '../../components/ReportCard.vue';
import { FileText, Clock, CheckCircle, XCircle, Inbox, PlusCircle } from 'lucide-vue-next';

const reportsStore = useReportsStore();

const reports = ref([]);
const stats = reactive({ total: 0, pending: 0, resolved: 0, rejected: 0 });
const loading = ref(false);
const activeFilter = ref('all');

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Resolved', value: 'resolved' },
  { label: 'Rejected', value: 'rejected' },
];

const filteredReports = computed(() => {
  if (activeFilter.value === 'all') return reports.value;
  return reports.value.filter(r => r.status?.toLowerCase() === activeFilter.value);
});

const filterCount = (val) => {
  if (val === 'all') return reports.value.length;
  return reports.value.filter(r => r.status?.toLowerCase() === val).length;
};

onMounted(async () => {
  loading.value = true;
  try {
    await reportsStore.fetchMyReports();
    reports.value = reportsStore.reports;
    stats.total = reportsStore.pagination.total;
    stats.pending = reports.value.filter(r => r.status === 'pending').length;
    stats.resolved = reports.value.filter(r => r.status === 'resolved').length;
    stats.rejected = reports.value.filter(r => r.status === 'rejected' || r.status === 'refused').length;
  } catch (error) {
    console.error('Error fetching reports:', error);
  } finally {
    loading.value = false;
  }
});

const formatStatus = (status) => {
  const map = { pending: 'Pending', resolved: 'Resolved', rejected: 'Refused', refused: 'Refused', escalated: 'Escalated' };
  return map[status?.toLowerCase()] || status;
};

const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  animation-delay: var(--delay, 0s);
}

/* Filter toolbar */
.filter-toolbar {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.filter-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0.875rem;
  border-radius: var(--radius-full);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-muted);
  background: var(--glass);
  border: 1px solid var(--glass-border);
  cursor: pointer;
  transition: all var(--transition-fast);
  font-family: inherit;
}

.filter-pill:hover {
  color: var(--text-secondary);
  border-color: var(--glass-border-hover);
}

.filter-pill.active {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.18) 0%, rgba(139, 92, 246, 0.1) 100%);
  color: var(--primary-light);
  border-color: rgba(99, 102, 241, 0.35);
}

.filter-count {
  background: var(--glass);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-full);
  padding: 0.05rem 0.4rem;
  font-size: 0.6875rem;
  font-weight: 700;
}

.filter-pill.active .filter-count {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.3);
}

/* Grid */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

/* Loading */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

/* Report date slot */
.report-date {
  font-size: 0.75rem;
  color: var(--text-muted);
}

/* Buttons (for empty state) */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
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

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  color: white;
  box-shadow: 0 4px 12px var(--primary-glow);
}

.btn-primary:hover { transform: translateY(-1px); }

/* Responsive */
@media (max-width: 900px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 540px) {
  .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
  .reports-grid { grid-template-columns: 1fr; }
}
</style>
