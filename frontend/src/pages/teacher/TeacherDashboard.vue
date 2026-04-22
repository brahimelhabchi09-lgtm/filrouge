<template>
    <div class="stats-grid animate-in">
      <div class="stat-card glass-card" style="--delay: 0s">
        <div class="stat-icon" style="background: var(--primary-light); color: white">
          <Users :size="22" />
        </div>
        <div>
          <p class="stat-label">Total Students</p>
          <p class="stat-value">{{ users.length || 0 }}</p>
        </div>
      </div>
      <div class="stat-card glass-card" style="--delay: 0.07s">
        <div class="stat-icon" style="background: var(--pending-bg); color: var(--pending)">
          <Clock :size="22" />
        </div>
        <div>
          <p class="stat-label">Pending Reports</p>
          <p class="stat-value">{{ reports.length || 0 }}</p>
        </div>
      </div>
      <div class="stat-card glass-card" style="--delay: 0.14s">
        <div class="stat-icon" style="background: var(--error-bg); color: var(--error)">
          <XCircle :size="22" />
        </div>
        <div>
          <p class="stat-label">Rejected Reports</p>
          <p class="stat-value">{{ rejectedReasons.length || 0 }}</p>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading your dashboard...</p>
    </div>

    <template v-else>
      <section class="section-block">
        <div class="section-header">
          <span class="section-title">
            Student Overview
            <span class="section-count">{{ users.length }}</span>
          </span>
        </div>
        <div class="glass-card table-card">
          <div class="table-container">
            <table v-if="users.length">
              <thead>
                <tr>
                  <th>Student Info</th>
                  <th>Reports Submitted</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id" class="table-row">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar">{{ userInitials(user) }}</div>
                      <div>
                        <div class="user-name">{{ user.first_name }} {{ user.last_name }}</div>
                        <div class="user-email">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="report-badge">{{ getUserReportCount(user.id) }} Reports</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="empty-state">
              <Users :size="36" style="opacity: 0.5" />
              <p>No students found.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="section-block">
        <div class="section-header">
          <span class="section-title">
            Pending Reports
            <span class="section-count">{{ reports.length }}</span>
          </span>
        </div>
        
        <div v-if="reportsLoading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading reports...</p>
        </div>
        
        <div v-else-if="reports.length === 0" class="empty-state">
          <CheckCircle2 :size="48" style="opacity: 0.5; color: var(--success)" />
          <p>You're all caught up! No pending reports.</p>
        </div>
        
        <div v-else class="reports-grid">
          <report-card 
            v-for="report in reports"
            :key="report.id"
            :title="report.title || report.message?.substring(0, 60) || 'Report from Student'"
            :category="report.category?.name || 'General'"
            :priority="report.priority || report.generated_report?.priority || 'P2'"
            :status="formatStatus(report.status)"
            :description="report.description || report.message || 'No description provided.'"
            :affected="`${report.student?.first_name || 'Unknown'} ${report.student?.last_name || 'Student'}`"
          >
            <div class="report-meta">
              <span class="report-date"><Calendar :size="12" /> {{ formatDate(report.created_at) }}</span>
            </div>
            <div class="action-buttons">
              <button class="btn btn-success btn-sm" @click="resolveReport(report)">
                <CheckCircle2 :size="14" />
                <span>Resolve</span>
              </button>
              <button class="btn btn-danger btn-sm" @click="openRefusalModal(report)">
                <XCircle :size="14" />
                <span>Reject</span>
              </button>
            </div>
          </report-card>
        </div>
        
        <pagination 
          v-if="reportsPagination.total > reportsPagination.perPage"
          :current-page="reportsPagination.currentPage"
          :total-pages="Math.ceil(reportsPagination.total / reportsPagination.perPage)"
          @page-change="fetchReports"
        />
      </section>

      <section v-if="rejectedReasons.length > 0" class="section-block">
        <div class="section-header">
          <span class="section-title">Rejected Reports</span>
        </div>
        <div class="reports-grid">
          <report-card 
            v-for="reason in rejectedReasons"
            :key="reason.id"
            :title="reason.generated_report?.message?.substring(0, 60) || 'Rejected Request'"
            category="Historical"
            priority="P3"
            status="Rejected"
            :description="reason.message"
          >
            <div class="report-meta">
              <span class="report-date"><Calendar :size="12" /> Rejected on {{ formatDate(reason.created_at) }}</span>
            </div>
          </report-card>
        </div>
      </section>
    </template>

    <refusal-modal 
      :is-open="showRefusalModal"
      @close="showRefusalModal = false"
      @submit="handleRejection"
    />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTeacherStore } from '../../stores/teacher';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import ReportCard from '../../components/ReportCard.vue';
import RefusalModal from '../../components/RefusalModal.vue';
import Pagination from '../../components/Pagination.vue';
import { Users, Clock, XCircle, CheckCircle2, Calendar } from 'lucide-vue-next';

const teacherStore = useTeacherStore();

const reports = ref([]);
const rejectedReasons = ref([]);
const users = ref([]);
const loading = ref(false);
const reportsLoading = ref(false);
const showRefusalModal = ref(false);
const selectedReport = ref(null);
const reportsPagination = ref({ currentPage: 1, perPage: 10, total: 0 });

onMounted(async () => {
  await fetchData();
});

const fetchData = async () => {
  loading.value = true;
  try {
    const [dashboardData, usersData] = await Promise.all([
      teacherStore.fetchDashboard(),
      teacherStore.fetchUsers(),
      fetchReports()
    ]);
    reports.value = dashboardData.pendingReports || [];
    rejectedReasons.value = dashboardData.rejectedReasons || [];
    users.value = usersData || [];
  } catch (error) {
    console.error('Error fetching data:', error);
  } finally {
    loading.value = false;
  }
};

const fetchReports = async (page = 1) => {
  reportsLoading.value = true;
  try {
    const response = await teacherStore.fetchReports(page);
    reports.value = response.data || [];
    reportsPagination.value = {
      currentPage: response.meta?.current_page || 1,
      perPage: response.meta?.per_page || 10,
      total: response.meta?.total || 0
    };
  } catch (error) {
    console.error('Error fetching reports:', error);
  } finally {
    reportsLoading.value = false;
  }
};

const getUserReportCount = (userId) => {
  return reports.value.filter(r => r.student_id === userId).length;
};

const userInitials = (user) => {
  return ((user.first_name || '')[0] + (user.last_name || '')[0]).toUpperCase();
};

const formatStatus = (status) => {
  const statusMap = { pending: 'Pending', resolved: 'Resolved', rejected: 'Rejected' };
  return statusMap[status?.toLowerCase()] || status;
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};

const resolveReport = async (report) => {
  try {
    await teacherStore.resolveReport(report.id);
    await fetchData();
  } catch (error) {
    console.error('Error resolving report:', error);
  }
};

const openRefusalModal = (report) => {
  selectedReport.value = report;
  showRefusalModal.value = true;
};

const handleRejection = async (reason) => {
  if (!selectedReport.value) return;
  
  try {
    await teacherStore.rejectReport(selectedReport.value.id, reason);
    showRefusalModal.value = false;
    await fetchData();
  } catch (error) {
    console.error('Error rejecting report:', error);
  }
};
</script>

<style scoped>
/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  animation-delay: var(--delay, 0s);
  border-top: 3px solid transparent;
  border-image: linear-gradient(90deg, rgba(255,255,255,0.05), rgba(255,255,255,0.1)) 1;
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  margin: 0 0 0.25rem 0;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0;
  color: var(--text-main);
  line-height: 1;
}

/* Sections */
.section-block {
  margin-bottom: 3rem;
  animation: fade-in 0.3s ease;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-main);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-count {
  font-size: 0.75rem;
  background: var(--surface-hover);
  padding: 0.15rem 0.6rem;
  border-radius: var(--radius-full);
  color: var(--text-secondary);
  font-weight: 600;
}

/* Tables */
.table-card {
  overflow: hidden;
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: var(--surface-hover);
  border-bottom: 1px solid var(--glass-border);
}

th {
  padding: 1rem 1.25rem;
  text-align: left;
  font-weight: 700;
  color: var(--text-muted);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table-row td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--glass-border);
}

.table-row:last-child td {
  border-bottom: none;
}

.table-row:hover td {
  background: var(--glass);
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, var(--emerald-500, #10b981) 0%, #059669 100%);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.875rem;
  color: white;
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
}

.user-name {
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 0.125rem;
}

.user-email {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.report-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.625rem;
  background: var(--glass);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
}

/* Reports */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.5rem;
}

.report-meta {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.report-date {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: all var(--transition-fast);
}

.btn-sm {
  padding: 0.4rem 0.75rem;
  font-size: 0.75rem;
}

.btn-success { background: var(--success); color: white; }
.btn-success:hover { background: #059669; }

.btn-danger { background: var(--error); color: white; }
.btn-danger:hover { background: #dc2626; }

/* Empty / Loading */
.empty-state, .loading-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

@media (max-width: 900px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
  .stats-grid { grid-template-columns: 1fr; gap: 1rem; }
  .reports-grid { grid-template-columns: 1fr; }
}
</style>
